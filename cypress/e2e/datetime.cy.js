describe('Test datetime component', () => {

    beforeEach(() => {
        cy.visit('/datetime');
    });

    it('datetime attributes', () => {
        cy.get('datetime-component')
            .find('input')
            .should('have.attr', 'name', 'datetime_allattributes')
            .and('have.attr', 'type', 'datetime-local')
            .and('have.attr', 'placeholder', 'placeholder value for input')
            .and('have.prop', 'value', '');
        
        cy.get('datetime-component')
            .find('input')
            .should('have.attr', 'readonly');
    });

    it('datetime validation', () => {
        cy.get('datetime-component')
            .find('label')
            .should('not.contain.text', '*')
            .and('contain.text', 'datetime label');
    });

});
