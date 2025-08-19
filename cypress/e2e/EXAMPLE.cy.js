describe('Test EXAMPLE config', () => {

    beforeEach(() => {
        cy.visit('http://php-apache/EXAMPLE');
    })

    it('"static fieldset" link is clicked and its label becomes visible', () => {
        cy.contains('Static elements').click();
        cy.get('#fieldset0\\.fieldset_static > div.label').should('be.visible');
    });

    it('"dynamic elements" link is clicked and its dropdown component has prefilled value "Choice #2"', () => {
        cy.contains('Dynamic elements').click();
        cy.get('[name="fieldset0\\.fieldset_dynamic\\.dropdown1"]').should('have.value', 'Choice #2');
    });
});

