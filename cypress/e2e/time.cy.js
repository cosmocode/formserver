describe('Test time component', () => {

    beforeEach(() => {
        cy.visit('/time');
    });

    it('time attributes', () => {
        cy.get('time-component')
            .find('input')
            .should('have.attr', 'name', 'time_allattributes')
            .and('have.attr', 'type', 'time')
            .and('have.attr', 'placeholder', 'placeholder value for input')
            .and('have.prop', 'value', '');
        
        cy.get('time-component')
            .find('input')
            .should('have.attr', 'readonly');
    });

    it('time validation', () => {
        cy.get('time-component')
            .find('label')
            .should('not.contain.text', '*')
            .and('contain.text', 'time label');
    });

});
