// Added smooth scroll behavior to anchor links
document.querySelectorAll('a[href^="#"]').forEach(anchor => {
    anchor.addEventListener('click', function (e) {
        e.preventDefault();
        const targetId = this.getAttribute('href').substring(1);
        const target = document.getElementById(targetId);
        if (!target) return;
        const targetTop = target.getBoundingClientRect().top + window.pageYOffset;
        history.pushState({ id: targetId, top: targetTop }, null, '#' + targetId);
        target.scrollIntoView({
            behavior: 'smooth'
        });
    });
});

window.addEventListener('popstate', function(e) {
    if (e.state && e.state.id) {
        const target = document.getElementById(e.state.id);
        if (!target) return;
        window.scrollTo({
            top: e.state.top,
            behavior: 'smooth'
        });
    }
});
