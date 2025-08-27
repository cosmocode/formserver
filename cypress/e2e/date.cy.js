describe('Test date component', () => {

    beforeEach(() => {
        cy.visit('/date');
    });

    it('date attributes', () => {
        cy.get('date-component')
            .find('input')
            .should('have.attr', 'name', 'date_allattributes')
            .and('have.attr', 'type', 'date')
            .and('have.attr', 'placeholder', 'placeholder value for input')
            .and('have.prop', 'value', '');
        
        cy.get('date-component')
            .find('input')
            .should('have.attr', 'readonly');
    });

    it('date validation', () => {
        cy.get('date-component')
            .find('label')
            .should('not.contain.text', '*')
            .and('contain.text', 'date label');
    });

});
