
<?php
// ==========================================
// CRS - Help & Contact
// ==========================================

$faqs = [
    [
        'question' => 'How can I book a train ticket?',
        'answer' => 'Go to the Train Schedule page, select your departure and destination, choose your preferred train and seat, then follow the booking instructions.'
    ],
    [
        'question' => 'How can I view my booking?',
        'answer' => 'Open the My Bookings section to view your reservation details.'
    ],
    [
        'question' => 'Can I cancel my train booking?',
        'answer' => 'Cancellation depends on the booking policy. Please contact our support team for assistance.'
    ],
    [
        'question' => 'How can I download my e-ticket?',
        'answer' => 'Open your booking details to view or download your e-ticket, if this feature is available.'
    ],
    [
        'question' => 'How can I update my passenger profile?',
        'answer' => 'Open Passenger Profile and click Edit Profile to update your personal information.'
    ],
    [
        'question' => 'What should I do if I forget my password?',
        'answer' => 'Please contact the support team for assistance with your account.'
    ]
];
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <title>Help & Contact | CRS</title>

    <!-- Main CRS Stylesheet -->
    <link rel="stylesheet" href="assets/css/m5.css">

    <!-- Help & Contact JavaScript -->
    <script src="assets/js/m5_faq.js" defer></script>
</head>

<body>

<div class="help-container">

    <a href="index.php" class="help-back-link">
        &larr; Back to Home
    </a>

    <header class="help-header">
        <h1>Help & Contact</h1>
        <p>
            Welcome to CRS Support Centre.
            Find answers to common questions or contact our support team.
        </p>
    </header>

    <!-- FAQ SECTION -->
    <section class="help-section">

        <h2>Frequently Asked Questions</h2>

        <input
            type="search"
            id="faqSearch"
            class="faq-search"
            placeholder="Search questions... (e.g. booking, ticket, profile)"
            aria-label="Search frequently asked questions"
        >

        <div id="faqList">

            <?php foreach ($faqs as $faq): ?>

                <div class="faq-item">

                    <button
                        type="button"
                        class="faq-question"
                        aria-expanded="false"
                    >
                        <span>
                            <?= htmlspecialchars($faq['question'], ENT_QUOTES, 'UTF-8') ?>
                        </span>

                        <span class="faq-icon">+</span>
                    </button>

                    <div class="faq-answer">
                        <?= htmlspecialchars($faq['answer'], ENT_QUOTES, 'UTF-8') ?>
                    </div>

                </div>

            <?php endforeach; ?>

        </div>

        <p class="no-results" id="noResults">
            No matching questions found. Try another keyword.
        </p>

    </section>

    <!-- CONTACT INFORMATION -->
    <section class="help-section">

        <h2>Contact Information</h2>

        <div class="contact-grid">

            <div class="contact-card">
                <h3>Email Support</h3>
                <p>
                    <a href="mailto:support@crs.com">
                        support@crs.com
                    </a>
                </p>
            </div>

            <div class="contact-card">
                <h3>Phone Support</h3>
                <p>+60 3-0000 0000</p>
            </div>

            <div class="contact-card">
                <h3>Operating Hours</h3>
                <p>Monday - Friday<br>9:00 AM - 5:00 PM</p>
            </div>

            <div class="contact-card">
                <h3>Support Centre</h3>
                <p>
                    We are ready to assist with train reservations
                    and account enquiries.
                </p>
            </div>

        </div>

        <p class="help-note">
            Note: Contact details are sample placeholders.
            Replace them with your project's official details.
        </p>

    </section>

    <!-- CONTACT FORM -->
    <section class="help-section">

        <h2>Send Us a Message</h2>

        <form id="contactForm" class="contact-form" method="POST" action="">

            <label for="contactName">Full Name</label>
            <input
                type="text"
                id="contactName"
                name="fullname"
                placeholder="Enter your full name"
                required
            >

            <label for="contactEmail">Email Address</label>
            <input
                type="email"
                id="contactEmail"
                name="email"
                placeholder="Enter your email address"
                required
            >

            <label for="contactSubject">Subject</label>
            <select id="contactSubject" name="subject" required>
                <option value="">Select a subject</option>
                <option value="Booking Enquiry">Booking Enquiry</option>
                <option value="Ticket Enquiry">Ticket Enquiry</option>
                <option value="Cancellation">Cancellation</option>
                <option value="Account Problem">Account Problem</option>
                <option value="Other">Other</option>
            </select>

            <label for="contactMessage">Your Message</label>
            <textarea
                id="contactMessage"
                name="message"
                placeholder="Write your message here..."
                required
            ></textarea>

            <button type="submit" class="help-submit-btn">
                Send Message
            </button>

            <p
                id="formMessage"
                class="help-form-message"
                role="status"
                aria-live="polite"
                style="color: <?= ($formMessageType ?? '') === 'success' ? '#15803d' : '#dc2626' ?>;"
            >
            <?= htmlspecialchars($formMessage ?? '', ENT_QUOTES, 'UTF-8') ?>
            </p>

        </form>

    </section>

</div>

</body>
</html>
