describe('Test dropdown component', () => {

    beforeEach(() => {
        cy.visit('/dropdown');
    });

    it('dropdown attributes', () => {
        cy.get('dropdown-component')
            .first()
            .find('select')
            .should('have.attr', 'name', 'dropdown_allattributes')
            .and('not.have.attr', 'multiple');
        
        cy.get('dropdown-component')
            .first()
            .find('select')
            .should('not.have.attr', 'size');
    });

    it('dropdown validation', () => {
        cy.get('dropdown-component')
            .first()
            .find('label')
            .should('not.contain.text', '*')
            .and('contain.text', 'dropdown label');
    });

    it('dropdown options', () => {
        cy.get('dropdown-component')
            .first()
            .find('option')
            .should('have.length', 4); // including empty_label option
        
        cy.get('dropdown-component')
            .first()
            .find('option')
            .eq(0)
            .should('have.attr', 'value', '')
            .and('contain.text', 'choose an option');
        
        cy.get('dropdown-component')
            .first()
            .find('option')
            .eq(1)
            .should('have.attr', 'value', 'first choice [a nice link](https://www.cosmocode.de)')
            .and('be.selected');
        
        cy.get('dropdown-component')
            .first()
            .find('option')
            .eq(2)
            .should('have.attr', 'value', 'second choice');
        
        cy.get('dropdown-component')
            .first()
            .find('option')
            .eq(3)
            .should('have.attr', 'value', 'third choice');
    });

    it('dropdown multiselect attributes', () => {
        cy.get('dropdown-component')
            .eq(1)
            .find('select')
            .should('have.attr', 'name', 'dropdown_multiselect_allattributes')
            .and('have.attr', 'size', '3');
        
        cy.get('dropdown-component')
            .eq(1)
            .find('select')
            .should('have.attr', 'multiple');
    });

    it('dropdown multiselect validation', () => {
        cy.get('dropdown-component')
            .eq(1)
            .find('label')
            .should('not.contain.text', '*')
            .and('contain.text', 'dropdown multiselect label');
    });

    it('dropdown multiselect options', () => {
        cy.get('dropdown-component')
            .eq(1)
            .find('option')
            .should('have.length', 3); // no empty_label in multiselect
        
        cy.get('dropdown-component')
            .eq(1)
            .find('option')
            .eq(0)
            .should('have.attr', 'value', 'first choice [a nice link](https://www.cosmocode.de)')
            .and('be.selected');
        
        cy.get('dropdown-component')
            .eq(1)
            .find('option')
            .eq(1)
            .should('have.attr', 'value', 'second choice');
        
        cy.get('dropdown-component')
            .eq(1)
            .find('option')
            .eq(2)
            .should('have.attr', 'value', 'third choice');
    });

});
