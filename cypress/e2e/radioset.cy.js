describe('Test radioset component', () => {

    beforeEach(() => {
        cy.visit('/radioset');
    });

    it('radioset attributes', () => {
        cy.get('radioset-component')
            .find('input[type="radio"]')
            .should('have.length', 3)
            .each(($radio) => {
                cy.wrap($radio)
                    .should('have.attr', 'name', 'radio_allattributes')
                    .and('have.attr', 'type', 'radio');
            });
    });

    it('radioset validation', () => {
        cy.get('radioset-component')
            .find('label.label')
            .should('contain.text', '*')
            .and('contain.text', 'radioset label');
    });

    it('radioset options', () => {
        cy.get('radioset-component')
            .find('input[type="radio"]')
            .eq(0)
            .should('have.attr', 'value', 'First choice');
        
        cy.get('radioset-component')
            .find('input[type="radio"]')
            .eq(1)
            .should('have.attr', 'value', 'Second choice');
        
        cy.get('radioset-component')
            .find('input[type="radio"]')
            .eq(2)
            .should('have.attr', 'value', 'Third choice');
    });


});
