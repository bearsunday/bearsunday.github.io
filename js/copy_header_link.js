// Copy the inline link to clipboard when clicking on the header
const HEADING_TAGS = 'h1, h2, h3, h4';
const changeCursorToPointer = (e) => {
    e.target.style.cursor = 'pointer';
};
const copyInlineLinkToClipboard = async (e) => {
    const el = document.createElement('textarea');
    el.value = window.location.href.split('#')[0] + '#' + e.target.id;
    document.body.appendChild(el);
    el.select();
    try {
        await navigator.clipboard.writeText(el.value);
        alert('Inline link copied to clipboard!');
    } catch (err) {
        console.error('Failed to copy inline link: ', err);
    }
    document.body.removeChild(el);
};
document.querySelectorAll(HEADING_TAGS).forEach((elem) => {
    elem.addEventListener('mouseover', changeCursorToPointer);
    elem.addEventListener('click', copyInlineLinkToClipboard);
});
