describe('Test static components', () => {

    beforeEach(() => {
        cy.visit('/static_components');
    });

    it('markdown with css class', () => {
        cy.get('markdown-component').should('have.html', '<div class="column is-full"><div class="field"><div class="control with-tooltip"><h2 class="has-text-centered subtitle is-3"><strong>Styled Headline</strong></h2>\n' +
            '</div></div></div>');
    });
    it('image component html', () => {
        cy.get('image-component')
            .should('have.html', '<div class="column is-full"><div class="field"><label class="label">Image</label><div class="control"><img src="/download/static_components?file=logo.png"></div></div></div>');
    });
    it('image component html', () => {
        cy.get('img')
            .should('be.visible');
    });
    it('download', () => {
        cy.get('download-component').should('have.html', '<div class="column is-full"><div class="field"><div class="control with-tooltip"><a href="/download/static_components?file=logo.png" target="_blank">Download logo.png from data dir</a></div></div></div>');
    });
    it('hr with styles', () => {
        cy.get('hr-component').should('have.html', '<div class="column is-full">\n' +
            '            <hr style="background-color: #f5f5f5; height: 2px;">\n' +
            '        </div>');
    });
});
