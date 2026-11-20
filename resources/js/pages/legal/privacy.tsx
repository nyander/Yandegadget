import { ContactLines, LegalPage } from '@/components/legal/legal-page';
import type { LegalProps } from '@/components/legal/legal-page';

export default function Privacy({
    contact,
    dpcRegistration,
    updatedOn,
}: LegalProps) {
    return (
        <LegalPage title="Privacy policy" updatedOn={updatedOn}>
            <p>
                Yande Gadgets is a second-hand electronics shop in Legon
                Boundary, Accra. This policy explains what personal information
                we keep when you use this website or buy from our store, why we
                keep it, and your rights under Ghana&rsquo;s Data Protection
                Act, 2012 (Act 843). We are the data controller for this
                information.
                {dpcRegistration &&
                    ` We are registered with the Data Protection Commission (registration ${dpcRegistration}).`}
            </p>

            <h2>What we collect</h2>
            <ul>
                <li>
                    <strong>Your account:</strong> your name, email address and
                    a scrambled (hashed) copy of your password. If you turn on
                    two-factor sign-in or passkeys, we keep what is needed to
                    check them.
                </li>
                <li>
                    <strong>Item requests:</strong> what you asked us to find,
                    the category and condition you chose, and the status of the
                    request.
                </li>
                <li>
                    <strong>Deposits:</strong> the amount, date, payment
                    reference and the method used (for example mobile money or
                    card). Paystack processes the payment. We never see or keep
                    your full card number or mobile money PIN.
                </li>
                <li>
                    <strong>In-store purchases:</strong> staff may record your
                    name and phone number with a sale so we can help with any
                    problem later. This is optional.
                </li>
                <li>
                    <strong>Suppliers:</strong> business name, contact person,
                    address, phone and email, and the items you offer us.
                </li>
                <li>
                    <strong>Technical information:</strong> your IP address and
                    browser type, kept with your sign-in session and in error
                    reports so we can keep the site secure and fix faults.
                </li>
            </ul>

            <h2>Why we use it</h2>
            <ul>
                <li>To run your account and sign you in securely.</li>
                <li>
                    To find the items you request, take your deposit, and tell
                    you when your item is ready.
                </li>
                <li>
                    To keep sales and payment records, which the law requires us
                    to keep.
                </li>
                <li>To protect the site from fraud and misuse.</li>
            </ul>
            <p>
                We use your information only for these purposes. We do not sell
                it, and we do not send marketing messages unless you ask us to.
            </p>

            <h2>Who we share it with</h2>
            <p>
                Only with the companies that help us run the shop, and only what
                they need:
            </p>
            <ul>
                <li>Paystack, to take deposits and refunds.</li>
                <li>Our email provider, to send account and request emails.</li>
                <li>
                    Our hosting, backup and error-reporting providers, who store
                    the site and its data for us.
                </li>
            </ul>
            <p>
                Some of these providers store data outside Ghana. We only use
                providers that protect it to a standard at least as high as
                Ghanaian law requires. We will also share information if the law
                or a court requires it.
            </p>

            <h2>How long we keep it</h2>
            <ul>
                <li>
                    Your account stays until you ask us to close it. When we
                    close it, we remove your name and email.
                </li>
                <li>
                    Sales, deposit and payment records are kept for as long as
                    Ghana&rsquo;s tax and accounting laws require, then deleted.
                </li>
                <li>Backups are kept for up to four months.</li>
            </ul>

            <h2>Keeping it safe</h2>
            <p>
                The site uses an encrypted connection (HTTPS). Passwords are
                stored hashed. Staff can only see what their job needs, and
                backups are encrypted.
            </p>

            <h2>Cookies</h2>
            <p>
                We use only the cookies the site needs to work: one that keeps
                you signed in, one that protects forms from forgery, and one
                that remembers whether you chose light or dark mode. We do not
                use advertising or tracking cookies.
            </p>

            <h2>Your rights</h2>
            <p>Under the Data Protection Act, 2012 you can ask us to:</p>
            <ul>
                <li>
                    tell you what information we hold about you and give you a
                    copy;
                </li>
                <li>correct anything that is wrong or out of date;</li>
                <li>
                    delete information we no longer need, or close your account;
                </li>
                <li>stop using your information for a particular purpose.</li>
            </ul>
            <p>
                You can update your name and email yourself under Settings when
                signed in. For anything else, contact us below. We will reply
                within 21 days. If you are unhappy with our answer, you can
                complain to the Data Protection Commission of Ghana.
            </p>

            <h2>Contact us</h2>
            <ContactLines contact={contact} />

            <h2>Changes to this policy</h2>
            <p>
                If we change this policy we will update the date at the top of
                this page. For important changes we will also email account
                holders.
            </p>
        </LegalPage>
    );
}
