/**
 * Guided tours of the admin panel (Filament). The steps for the current page
 * come from App\Support\AdminTours as JSON in #yg-tour-data. Tours start on a
 * page's first visit while tips are on, or any time from the help menu.
 * Preferences live in this browser only.
 *
 * The tour follows what the person does: when a panel or pop-up opens, the
 * page tour pauses and the panel's own tips (marked with data-tour-panel)
 * take over; when it closes, the page tour carries on where it was. After
 * Livewire redraws part of the page (a tab, a filter), the highlight moves to
 * the redrawn element.
 */
import { driver, type DriveStep, type Driver } from 'driver.js';
import 'driver.js/dist/driver.css';
import './tour.css';

type TourStep = { element: string | null; title: string; text: string };
type TourData = {
    key: string;
    steps: TourStep[];
    panels?: Record<string, TourStep[]>;
};

const TIPS_KEY = 'yg-tips';
const SEEN_PREFIX = 'yg-tour-seen:';
const PANEL_PREFIX = 'panel:';

function read(key: string): string | null {
    try {
        return window.localStorage.getItem(key);
    } catch {
        return null;
    }
}

function write(key: string, value: string | null): void {
    try {
        if (value === null) {
            window.localStorage.removeItem(key);
        } else {
            window.localStorage.setItem(key, value);
        }
    } catch {
        // Private mode or storage blocked: tips just won't be remembered.
    }
}

function tourData(): TourData | null {
    const node = document.getElementById('yg-tour-data');

    if (!node?.textContent) {
        return null;
    }

    try {
        return JSON.parse(node.textContent) as TourData;
    } catch {
        return null;
    }
}

function isVisible(element: Element): boolean {
    const box = element.getBoundingClientRect();

    return box.width > 0 && box.height > 0;
}

let current: Driver | null = null;
let pending: number | undefined;

/** The page tour, paused while a panel is open: which step to come back to. */
let paused: { index: number } | null = null;

/** The panel tour, if one is running, and the pop-up it belongs to. */
let panelTour: { driver: Driver; modal: Element; key: string } | null = null;

/** Panels whose tips have shown during the current page tour, so they don't repeat. */
const shownThisTour = new Set<string>();

/** Set while closing a tour on purpose, so it isn't marked as seen. */
let quiet = false;

function find(
    selector: string,
    scope: ParentNode = document,
): Element | undefined {
    return Array.from(scope.querySelectorAll(selector)).find(isVisible);
}

/** Steps whose part of the page is there; each finds its element again when shown. */
function driveSteps(
    steps: TourStep[],
    scope: ParentNode = document,
): DriveStep[] {
    return steps.flatMap((step) => {
        const popover = { title: step.title, description: step.text };

        if (step.element === null) {
            return [{ popover }];
        }

        const selector = step.element;
        const first = find(selector, scope);

        if (!first) {
            return [];
        }

        // Livewire may have replaced the element by the time the step shows.
        return [{ element: () => find(selector, scope) ?? first, popover }];
    });
}

function makeTour(steps: DriveStep[], onDone: () => void): Driver {
    return driver({
        steps,
        showProgress: steps.length > 1,
        progressText: '{{current}} of {{total}}',
        showButtons: ['next', 'previous', 'close'],
        nextBtnText: 'Next',
        prevBtnText: 'Back',
        doneBtnText: 'Done',
        allowClose: true,
        // driver.js ignores Next, Back and Done while it animates between
        // steps, which meant clicking twice. Without the slide, every click counts.
        animate: false,
        overlayOpacity: 0.55,
        stagePadding: 6,
        stageRadius: 10,
        popoverClass: 'yg-tour',
        onPopoverRender: (popover, { driver: active }) => {
            if (active.isLastStep()) {
                return;
            }

            const skip = document.createElement('button');
            skip.type = 'button';
            skip.className = 'yg-tour__skip';
            skip.textContent = 'Skip tour';
            skip.addEventListener('click', () => active.destroy());
            popover.footer.prepend(skip);
        },
        onDestroyed: () => {
            if (!quiet) {
                onDone();
            }
        },
    });
}

function start(retriesLeft = 0, from = 0): void {
    const data = tourData();

    if (!data) {
        return;
    }

    // Parts of the page can still be drawing after a page change; give them a moment.
    const missing = data.steps.some(
        (step) => step.element !== null && !find(step.element),
    );

    if (missing && retriesLeft > 0) {
        pending = window.setTimeout(() => start(retriesLeft - 1, from), 400);

        return;
    }

    // A pop-up opened while we waited: hold the tour until it closes.
    if (topModal()) {
        paused = { index: from };

        return;
    }

    // Never run two tours at once: each would need its own Next and Done.
    if (current?.isActive()) {
        stopQuietly(current);
        current = null;
    }

    // Skip steps whose part of the page isn't there (an empty table, a hidden widget).
    const steps = driveSteps(data.steps);

    if (steps.length === 0) {
        return;
    }

    if (from === 0) {
        shownThisTour.clear();
    }

    const tour = makeTour(steps, () => {
        write(SEEN_PREFIX + data.key, '1');
        current = null;
        paused = null;
    });

    current = tour;
    tour.drive(Math.min(from, steps.length - 1));
}

function stopQuietly(tour: Driver): void {
    quiet = true;
    tour.destroy();
    quiet = false;
}

/** The pop-up on top, if any. Filament marks open ones with .fi-modal-open. */
function topModal(): Element | null {
    const open = Array.from(document.querySelectorAll('.fi-modal-open')).filter(
        (modal) => modal.querySelector('.fi-modal-window') !== null,
    );

    return open.at(-1) ?? null;
}

let lastModal: Element | null = null;
let lastPanelKey: string | null = null;

/** Pause, start or resume tours as pop-ups open and close. */
function followModals(): void {
    const modal = topModal();
    const marker = modal?.querySelector('[data-tour-panel]') ?? null;
    const panelKey = marker?.getAttribute('data-tour-panel') ?? null;

    if (modal === lastModal && panelKey === lastPanelKey) {
        return;
    }

    lastModal = modal;
    lastPanelKey = panelKey;

    // Something opened on top of a running page tour: pause it.
    if (modal && current?.isActive()) {
        paused = { index: current.getActiveIndex() ?? 0 };
        stopQuietly(current);
        current = null;
    }

    // The pop-up the panel tour belonged to has gone or changed: they've moved on.
    if (panelTour && panelTour.modal !== modal) {
        write(SEEN_PREFIX + PANEL_PREFIX + panelTour.key, '1');
        stopQuietly(panelTour.driver);
        panelTour = null;
    }

    if (modal && panelKey && !panelTour) {
        startPanel(panelKey, modal);
    }

    // Everything closed: carry on with the page tour.
    if (!modal && paused) {
        const from = paused.index;
        paused = null;
        window.clearTimeout(pending);
        pending = window.setTimeout(() => {
            // A pop-up inside a panel briefly closes the panel; only carry on
            // once nothing is open.
            if (topModal()) {
                paused = { index: from };

                return;
            }

            start(2, from);
        }, 500);
    }
}

function startPanel(key: string, modal: Element, retriesLeft = 4): void {
    const steps = tourData()?.panels?.[key];
    const seen = read(SEEN_PREFIX + PANEL_PREFIX + key) !== null;

    // Show a panel's tips the first time, or once while the person is following a tour.
    const following = paused !== null && !shownThisTour.has(key);

    if (!steps || (!following && (seen || !tipsEnabled()))) {
        return;
    }

    // Wait for the slide-over to finish sliding in before looking for its parts.
    window.setTimeout(() => {
        if (topModal() !== modal || panelTour) {
            return;
        }

        const driveable = driveSteps(steps, modal);

        if (driveable.length < steps.length && retriesLeft > 0) {
            startPanel(key, modal, retriesLeft - 1);

            return;
        }

        if (driveable.length === 0) {
            return;
        }

        const tour = makeTour(driveable, () => {
            write(SEEN_PREFIX + PANEL_PREFIX + key, '1');
            panelTour = null;
        });

        panelTour = { driver: tour, modal, key };
        shownThisTour.add(key);
        tour.drive();
    }, 350);
}

/**
 * After Livewire redraws, keep the highlight on the (possibly new) element.
 *
 * Only steps in when the redraw actually affected the highlighted element:
 * driver.js ignores Next, Back and Done while a step is animating, so moving
 * the step on every update (notification checks, widgets) swallowed clicks.
 */
function followRedraws(): void {
    for (const tour of [current, panelTour?.driver]) {
        if (!tour?.isActive()) {
            continue;
        }

        const element = tour.getActiveElement();
        const index = tour.getActiveIndex();

        // A message in the middle of the screen has nothing to follow.
        if (
            !element ||
            element.id === 'driver-dummy-element' ||
            index === undefined
        ) {
            continue;
        }

        if (!element.isConnected) {
            // Replaced by the redraw: highlight the new one.
            tour.moveTo(index);
        } else if (!element.classList.contains('driver-active-element')) {
            // Livewire removed the highlight class while updating it: put it back.
            element.classList.add('driver-active-element');
        }
    }
}

let scheduled = false;

function schedule(): void {
    if (scheduled) {
        return;
    }

    scheduled = true;
    window.requestAnimationFrame(() => {
        scheduled = false;
        followModals();
    });
}

/** The tour's own overlay and popover change the page too; ignore those. */
function isTourNode(node: Node): boolean {
    const element = node instanceof Element ? node : node.parentElement;

    return element?.closest('.driver-popover, .driver-overlay') != null;
}

function tipsEnabled(): boolean {
    return read(TIPS_KEY) !== 'off';
}

function setTips(on: boolean): void {
    write(TIPS_KEY, on ? null : 'off');

    if (on) {
        // Start afresh: every page shows its tour again on the next visit.
        try {
            Object.keys(window.localStorage)
                .filter((key) => key.startsWith(SEEN_PREFIX))
                .forEach((key) => window.localStorage.removeItem(key));
        } catch {
            // Ignore: storage unavailable.
        }
    }
}

declare global {
    interface Window {
        ygTourReady?: boolean;
        YandeTour: {
            start: () => void;
            tipsEnabled: () => boolean;
            setTips: (on: boolean) => void;
            hasTour: () => boolean;
        };
    }
}

// First visit to a page with a tour: start it once the widgets have loaded.
// The admin moves between pages without reloading (Filament's SPA mode), so
// this runs again after each navigation, and a running tour closes when the
// page changes.
function autoStart(): void {
    window.clearTimeout(pending);
    const data = tourData();

    if (data && tipsEnabled() && read(SEEN_PREFIX + data.key) === null) {
        pending = window.setTimeout(() => start(4), 700);
    }
}

// Set up once, even if the admin loads this script again after a page change:
// a second copy would run its own tours alongside this one.
if (!window.ygTourReady) {
    window.ygTourReady = true;

    window.YandeTour = {
        // From the help menu: start over from the first step.
        start: () => {
            window.clearTimeout(pending);
            paused = null;
            start();
        },
        tipsEnabled,
        setTips,
        hasTour: () => tourData() !== null,
    };

    // Pop-ups opening or closing (their class changes), or their content arriving.
    new MutationObserver((records) => {
        const relevant = records.some((record) =>
            record.type === 'attributes'
                ? (record.target as Element).classList.contains('fi-modal')
                : !isTourNode(record.target) &&
                  ![...record.addedNodes, ...record.removedNodes].every(
                      isTourNode,
                  ),
        );

        if (relevant) {
            schedule();
        }
    }).observe(document.body, {
        subtree: true,
        childList: true,
        attributes: true,
        attributeFilter: ['class'],
    });

    type LivewireHooks = {
        hook: (
            name: 'commit',
            callback: (args: { succeed: (fn: () => void) => void }) => void,
        ) => void;
    };

    // After each Livewire update (a tab, a filter, a sort), follow the redrawn page.
    function hookLivewire(livewire: LivewireHooks): void {
        livewire.hook('commit', ({ succeed }) =>
            succeed(() =>
                window.requestAnimationFrame(() => {
                    followModals();
                    followRedraws();
                }),
            ),
        );
    }

    const livewire = (window as unknown as { Livewire?: LivewireHooks })
        .Livewire;

    if (livewire) {
        hookLivewire(livewire);
    } else {
        document.addEventListener('livewire:init', () => {
            const ready = (window as unknown as { Livewire?: LivewireHooks })
                .Livewire;

            if (ready) {
                hookLivewire(ready);
            }
        });
    }

    document.addEventListener('livewire:navigating', () => {
        window.clearTimeout(pending);
        paused = null;

        if (panelTour) {
            stopQuietly(panelTour.driver);
            panelTour = null;
        }

        current?.destroy();
    });
    document.addEventListener('livewire:navigated', autoStart);
    autoStart();
}
