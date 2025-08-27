describe('Test email component', () => {

    beforeEach(() => {
        cy.visit('/email');
    });

    it('email attributes', () => {
        cy.get('email-component')
            .find('input')
            .should('have.attr', 'name', 'email_allattributes')
            .and('have.attr', 'type', 'email')
            .and('have.attr', 'placeholder', 'placeholder value for input')
            .and('have.prop', 'value', '');
        
        cy.get('email-component')
            .find('input')
            .should('have.attr', 'readonly');
    });

    it('email validation', () => {
        cy.get('email-component')
            .find('label')
            .should('not.contain.text', '*')
            .and('contain.text', 'email label');
    });

});
