import { ContactLines, LegalPage } from '@/components/legal/legal-page';
import type { LegalProps } from '@/components/legal/legal-page';

export default function Terms({ contact, updatedOn }: LegalProps) {
    return (
        <LegalPage title="Terms of use" updatedOn={updatedOn}>
            <p>
                These terms cover this website and item requests. Buying in our
                store is also covered by your rights under Ghanaian consumer
                law, which these terms do not take away.
            </p>

            <h2>Prices and stock</h2>
            <ul>
                <li>
                    Items are bought in the UK and priced in pounds. The cedi
                    prices on the site use our current exchange rate and may
                    change when the rate does.
                </li>
                <li>
                    The website does not sell items. The final price is agreed
                    with you in the store, and the sale happens there.
                </li>
                <li>
                    Every item is second-hand and graded on its own. Condition
                    notes and photos describe that exact item.
                </li>
                <li>
                    Stock changes quickly. An item shown online may sell in the
                    store before you arrive, unless it has been reserved for
                    you.
                </li>
            </ul>

            <h2>Item requests and deposits</h2>
            <ul>
                <li>
                    If we don&rsquo;t have what you want, you can ask us to find
                    it. A request starts once you pay the deposit shown for the
                    condition you chose.
                </li>
                <li>
                    When we find a matching item we reserve it for you and let
                    you know. Your deposit comes off the price you agree in the
                    store.
                </li>
                <li>
                    If we can&rsquo;t find the item, or cancel your request, we
                    refund your deposit in full to the way you paid.
                </li>
                <li>
                    If you change your mind before we find an item, contact us
                    and we will refund your deposit. Once an item has been found
                    and reserved for you, ask us about cancelling.
                </li>
            </ul>

            <h2>Your account</h2>
            <ul>
                <li>
                    Keep your password private. You are responsible for what is
                    done with your account.
                </li>
                <li>
                    We may close accounts used for fraud or to misuse the site.
                </li>
            </ul>

            <h2>Privacy</h2>
            <p>
                Our privacy policy explains what information we keep and your
                rights over it.
            </p>

            <h2>Contact us</h2>
            <ContactLines contact={contact} />
        </LegalPage>
    );
}
