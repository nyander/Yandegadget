import { ContactLines, LegalPage } from '@/components/legal/legal-page';
import type { LegalProps } from '@/components/legal/legal-page';

export default function Terms({
    contact,
    cancellationFeePercent,
    collectionDays,
    updatedOn,
}: LegalProps) {
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
                    Once the item is in the store, please collect it within{' '}
                    {collectionDays} days. We tell you the date.
                </li>
            </ul>

            <h2>Cancelling and refunds</h2>
            <ul>
                <li>
                    <strong>Before we find your item:</strong> cancel at any
                    time from your request page and we refund the whole deposit.
                </li>
                <li>
                    <strong>If we can&rsquo;t supply it,</strong> or the item
                    isn&rsquo;t as described when you see it, we cancel the
                    request and refund the whole deposit.
                </li>
                {cancellationFeePercent > 0 ? (
                    <li>
                        <strong>After we have found your item:</strong> you can
                        still cancel, but we keep {cancellationFeePercent}% of
                        the deposit to cover the cost of buying and shipping it,
                        and refund the rest. The same applies if you don&rsquo;t
                        collect it within {collectionDays} days; it then goes
                        back on sale.
                    </li>
                ) : (
                    <li>
                        <strong>After we have found your item:</strong> you can
                        still cancel and get the whole deposit back. If you
                        don&rsquo;t collect it within {collectionDays} days it
                        goes back on sale and we refund the deposit.
                    </li>
                )}
                <li>
                    Refunds go back to the card or mobile money wallet you paid
                    with. Paystack usually completes them within a few working
                    days.
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
