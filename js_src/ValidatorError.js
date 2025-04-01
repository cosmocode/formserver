export class ValidatorError extends Error {
    constructor(componentName, message = "", ...args) {
        super(message, ...args);
        this.message = message;
        this.componentName = componentName;
    }
}
