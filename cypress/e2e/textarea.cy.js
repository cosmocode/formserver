describe('Test textarea component', () => {

    beforeEach(() => {
        cy.visit('/textarea');
    });

    it('textarea attributes', () => {
        cy.get('textarea-component')
            .find('textarea')
            .should('have.attr', 'name', 'textarea_allattributes')
            .and('have.attr', 'placeholder', 'placeholder value for input')
            .and('have.prop', 'value', '')
            .and('have.attr', 'rows', '7')
            .and('have.attr', 'cols', '40');
        
        cy.get('textarea-component')
            .find('textarea')
            .should('not.have.attr', 'readonly');
    });

    it('textarea validation', () => {
        cy.get('textarea-component')
            .find('label')
            .should('not.contain.text', '*')
            .and('contain.text', 'textarea label');
    });

});
