describe('Conditional Visibility Tests', () => {
    beforeEach(() => {
        cy.visit('/visible');
    });

    it('should show/hide text fields based on input conditions', () => {
        // Initially, the third field should be hidden
        cy.get('[name="fset1.text3"]').should('not.exist');

        // Type "show" in first field
        cy.get('[name="fset1.text1"]').type('show');

        // Third field should still be hidden (second condition not met)
        cy.get('[name="fset1.text3"]').should('not.exist');

        // Type "show" in second field
        cy.get('[name="fset1.text2"]').type('show');

        // Now third field should be visible
        cy.get('[name="fset1.text3"]').should('be.visible');

        // Test with "unhide" in second field
        cy.get('[name="fset1.text2"]').clear().type('unhide');
        cy.get('[name="fset1.text3"]').should('be.visible');

        // Clear first field - third should be hidden again
        cy.get('[name="fset1.text1"]').clear();
        cy.get('[name="fset1.text3"]').should('not.exist');
    });

    it('should show/hide fields based on dropdown selections', () => {
        // Initially, the sixth field should be hidden
        cy.get('[name="fset2.text4"]').should('not.exist');

        // Select "show" in fourth dropdown
        cy.get('[name="fset2.drop1"]').select('show');

        // Sixth field should still be hidden (second condition not met)
        cy.get('[name="fset2.text4"]').should('not.exist');

        // Select "show" in fifth dropdown
        cy.get('[name="fset2.drop2"]').select('show');

        // Now sixth field should be visible
        cy.get('[name="fset2.text4"]').should('be.visible');

        // Test with "unhide" in fifth dropdown
        cy.get('[name="fset2.drop2"]').select('unhide');
        cy.get('[name="fset2.text4"]').should('be.visible');

        // Select "hide" in fourth dropdown - sixth should be hidden again
        cy.get('[name="fset2.drop1"]').select('hide');
        cy.get('[name="fset2.text4"]').should('not.exist');
    });

    it('should show/hide fields based on mathematical conditions', () => {
        // Initially, the secret number field should be hidden
        cy.get('[name="fset3.num3"]').should('not.exist');

        // Enter numbers that sum to 10 or less
        cy.get('[name="fset3.num1"]').type('5');
        cy.get('[name="fset3.num2"]').type('5');

        // Secret field should still be hidden (sum = 10, not > 10)
        cy.get('[name="fset3.num3"]').should('not.exist');

        // Change second number to make sum > 10
        cy.get('[name="fset3.num2"]').clear().type('6');

        // Now secret field should be visible (sum = 11 > 10)
        cy.get('[name="fset3.num3"]').should('be.visible');

        // Change first number to make sum <= 10 again
        cy.get('[name="fset3.num1"]').clear().type('4');

        // Secret field should be hidden again (sum = 10)
        cy.get('[name="fset3.num3"]').should('not.exist');
    });

    it('should show/hide table rows based on dropdown selection', () => {
        // Initially, the conditional table row should be hidden
        cy.get('table tr').contains('Secret row').should('not.be.visible');

        // Select "extra row" - conditional row should become visible
        cy.get('[name="fset4.drop"]').select('extra row');
        cy.get('table tr').contains('Secret row').should('be.visible');

        // Switch back to "only one row" - conditional row should be hidden again
        cy.get('[name="fset4.drop"]').select('only one row');
        cy.get('table tr').contains('Secret row').should('not.be.visible');
    });

    it('should handle complex visibility expressions correctly', () => {
        // Test that all conditions must be met for visibility
        cy.get('[name="fset1.text1"]').type('show');
        cy.get('[name="fset1.text2"]').type('hide'); // not 'show' or 'unhide'
        cy.get('[name="fset1.text3"]').should('not.exist');

        // Test with partial match
        cy.get('[name="fset1.text2"]').clear().type('showing'); // contains 'show' but not exact match
        cy.get('[name="fset1.text3"]').should('not.exist');

        // Test with exact match
        cy.get('[name="fset1.text2"]').clear().type('show');
        cy.get('[name="fset1.text3"]').should('be.visible');
    });

});
