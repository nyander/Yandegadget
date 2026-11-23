/**
 * Guided tours of the admin panel (Filament). The steps for the current page
 * come from App\Support\AdminTours as JSON in #yg-tour-data. Tours start on a
 * page's first visit while tips are on, or any time from the help menu.
 * Preferences live in this browser only.
 */
import { driver, type DriveStep } from 'driver.js';
import 'driver.js/dist/driver.css';
import './tour.css';

type TourStep = { element: string | null; title: string; text: string };
type TourData = { key: string; steps: TourStep[] };

const TIPS_KEY = 'yg-tips';
const SEEN_PREFIX = 'yg-tour-seen:';

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

function start(): void {
    const data = tourData();

    if (!data) {
        return;
    }

    // Skip steps whose part of the page isn't there (an empty table, a hidden widget).
    const steps: DriveStep[] = data.steps.flatMap((step) => {
        const popover = { title: step.title, description: step.text };

        if (step.element === null) {
            return [{ popover }];
        }

        const element = Array.from(
            document.querySelectorAll(step.element),
        ).find(isVisible);

        return element ? [{ element, popover }] : [];
    });

    if (steps.length === 0) {
        return;
    }

    const markSeen = () => write(SEEN_PREFIX + data.key, '1');

    const tour = driver({
        steps,
        showProgress: steps.length > 1,
        progressText: '{{current}} of {{total}}',
        showButtons: ['next', 'previous', 'close'],
        nextBtnText: 'Next',
        prevBtnText: 'Back',
        doneBtnText: 'Done',
        allowClose: true,
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
        onDestroyed: markSeen,
    });

    tour.drive();
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
        YandeTour: {
            start: () => void;
            tipsEnabled: () => boolean;
            setTips: (on: boolean) => void;
            hasTour: () => boolean;
        };
    }
}

window.YandeTour = {
    start,
    tipsEnabled,
    setTips,
    hasTour: () => tourData() !== null,
};

// First visit to a page with a tour: start it once the widgets have loaded.
const data = tourData();

if (data && tipsEnabled() && read(SEEN_PREFIX + data.key) === null) {
    window.setTimeout(start, 900);
}
