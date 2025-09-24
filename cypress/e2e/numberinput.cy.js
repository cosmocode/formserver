describe('Test numberinput component', () => {

    beforeEach(() => {
        cy.visit('/numberinput');
    });

    it('numberinput attributes', () => {
        cy.get('numberinput-component')
            .find('input')
            .should('have.attr', 'name', 'num_allattributes')
            .and('have.attr', 'type', 'number')
            .and('have.attr', 'placeholder', 'enter a number')
            .and('have.prop', 'value', '');
        
        cy.get('numberinput-component')
            .find('input')
            .should('have.attr', 'readonly');
    });

    it('numberinput validation', () => {
        cy.get('numberinput-component')
            .find('label')
            .should('not.contain.text', '*')
            .and('contain.text', 'number label');
    });


});
