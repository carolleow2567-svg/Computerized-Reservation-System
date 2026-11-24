
// ==========================================
// CRS - HELP & CONTACT INTERACTIONS
// ==========================================

// FAQ Accordion
const faqItems = document.querySelectorAll('.faq-item');

faqItems.forEach(item => {
    const question = item.querySelector('.faq-question');
    const icon = item.querySelector('.faq-icon');

    question.addEventListener('click', () => {
        const isOpen = item.classList.toggle('active');

        question.setAttribute('aria-expanded', isOpen);
        icon.textContent = isOpen ? '-' : '+';
    });
});


// FAQ Search
const faqSearch = document.getElementById('faqSearch');
const noResults = document.getElementById('noResults');

if (faqSearch) {
    faqSearch.addEventListener('input', function () {
        const keyword = this.value.toLowerCase().trim();
        let visibleCount = 0;

        faqItems.forEach(item => {
            const matches = item.textContent
                .toLowerCase()
                .includes(keyword);

            item.style.display = matches ? 'block' : 'none';

            if (matches) {
                visibleCount++;
            }
        });

        if (noResults) {
            noResults.style.display =
                visibleCount === 0 ? 'block' : 'none';
        }
    });
}

