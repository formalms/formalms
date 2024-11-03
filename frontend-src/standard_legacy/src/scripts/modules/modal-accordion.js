var ModalAccordion = (function() {
    // dates modal
    $(document)
        .on('click', '.js-edition-accordion', function() {
            const target = $(this).data('target');
            $(this).toggleClass('is-open');
            $('#' + target).slideToggle();
        });
})();

module.exports = ModalAccordion;