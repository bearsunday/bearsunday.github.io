// Added smooth scroll behavior to anchor links
document.querySelectorAll('a[href^="#"]').forEach(anchor => {
    anchor.addEventListener('click', function (e) {
        e.preventDefault();
        const target = document.querySelector(this.getAttribute('href'));
        const targetTop = target.getBoundingClientRect().top + window.pageYOffset;
        const targetId = this.getAttribute('href');
        history.pushState({ id: targetId, top: targetTop }, null, targetId);
        target.scrollIntoView({
            behavior: 'smooth'
        });
    });
});

window.addEventListener('popstate', function(e) {
    if (e.state && e.state.id) {
        const target = document.querySelector(e.state.id);
        window.scrollTo({
            top: e.state.top,
            behavior: 'smooth'
        });
    }
});
