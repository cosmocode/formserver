describe('Test textinput component', () => {

    beforeEach(() => {
        cy.visit('/textinput');
    });

    it('textinput attributes', () => {
        cy.get('textinput-component')
            .find('input')
            .should('have.attr', 'name', 'txt_allattributes')
            .and('have.attr', 'type', 'text')
            .and('have.attr', 'placeholder', 'placeholder value for input')
            .and('have.prop', 'value', '');
        
        cy.get('textinput-component')
            .find('input')
            .should('have.attr', 'readonly');
    });

    it('textinput validation', () => {
        cy.get('textinput-component')
            .find('label')
            .should('not.contain.text', '*')
            .and('contain.text', 'text label');
    });
});
