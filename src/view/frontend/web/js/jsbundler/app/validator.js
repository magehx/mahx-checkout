import JustValidate from 'just-validate'
import match from 'match-operator'

const customRules = {
    /**
     * Checks if a value is one of the allowed values.
     * @param {string} values - Comma-separated string of allowed values.
     * @returns {Function} Validator function.
     */
    oneOf: (values) => (value) => values.split(',').includes(value),
};

/**
 * Extracts rule details from a validation rule string.
 * @param {string} vRule - Validation rule string (e.g., "maxLength::10").
 * @returns {Array} [ruleName, ruleValue]
 */
const getRuleDetails = (vRule) => vRule.split('::');

/**
 * Matches a rule to a specific validation type.
 * @param {string} ruleName - The name of the rule.
 * @param {string|number} ruleParams - The rule's parameter value.
 * @returns {string|null} The mapped validation rule.
 */
const matchRule = (ruleName, ruleParams) => match(ruleName, {
    max_text_length: `maxLength::${ruleParams}`,
    min_text_length: `minLength::${ruleParams}`,
}) || null;

/**
 * Retrieves the correct form element for a given field.
 * @param {HTMLFormElement} form - The form element.
 * @param {Object} fieldsConfig - Field configuration object.
 * @param {string} field - Field name.
 * @returns {HTMLElement|null} The corresponding form element.
 */
const getFormElement = (form, fieldsConfig, field) => (
    fieldsConfig[field]?.type === 'multiline'
        ? form.elements[`${field}[0]`]
        : form.elements[field] || null
);

/**
 * Generates validation rules for a given field configuration.
 * @param {Object} config - Field validation configuration.
 * @returns {string|null} Concatenated validation rules.
 */
const getValidationRules = (config) => {
    if (!config) return null;

    const rules = [
        config.required ? 'required' : null,
        ...Object.entries(config.rules || {}).map(([ruleName, ruleParams]) => matchRule(ruleName, ruleParams)),
    ].filter(Boolean);

    return rules.length ? rules.join('|') : null;
};

const helper = {
    /**
     * Creates a JustValidate rule object.
     * @param {string} vRule - Validation rule string.
     * @returns {Object} JustValidate rule object.
     */
    createJRule(vRule) {
        const [jRuleName, jRuleValue] = getRuleDetails(vRule);

        return {
            rule: jRuleName,
            value: jRuleValue
                ? match(jRuleName, {
                    minLength: Number(jRuleValue),
                    maxLength: Number(jRuleValue),
                    minNumber: Number(jRuleValue),
                    maxMaxNumber: Number(jRuleValue),
                    minFilesCount: Number(jRuleValue),
                    maxFilesCount: Number(jRuleValue),
                    customRegexp: new RegExp(jRuleValue),
                }, jRuleValue)
                : undefined,
        };
    },

    /**
     * Creates a JustValidate custom validator object.
     * @param {string} vRule - Validation rule string.
     * @returns {Object} JustValidate validator object.
     */
    createJValidator(vRule) {
        const [jRuleName, jRuleValue] = getRuleDetails(vRule);
        return { validator: customRules[jRuleName]?.(jRuleValue) };
    },

    /**
     * Determines if a rule is a custom rule.
     * @param {string} vRule - Validation rule string.
     * @returns {boolean} True if custom rule, false otherwise.
     */
    isCustomRule(vRule) {
        const [jRuleName] = getRuleDetails(vRule);
        return !!customRules[jRuleName];
    },

    /**
     * Prepares JustValidate rules from a given element's rule string.
     * @param {Object} eRule - Element rule object.
     * @returns {Array} Array of JustValidate rules.
     */
    prepareJRules(eRule) {
        return eRule.rules
            .split('|')
            .map((rule) => (this.isCustomRule(rule) ? this.createJValidator(rule) : this.createJRule(rule)));
    },
};

/**
 * Prepares validation rules for all fields in the form.
 * @param {HTMLFormElement} form - The form element.
 * @param {Object} fieldsConfig - Field validation configuration.
 * @returns {Array} Array of validation rule objects.
 */
const prepareValidationRules = (form, fieldsConfig) => (
    Object.keys(fieldsConfig)
        .map((field) => {
            const config = fieldsConfig[field];
            const element = getFormElement(form, fieldsConfig, field);
            const rules = getValidationRules(config);
            return element && rules ? { element, rules } : null;
        })
        .filter(Boolean)
);

/**
 * Creates and configures a JustValidate instance.
 * @param {HTMLFormElement} form - The form element.
 * @param {Object} globalConfig - Global configuration settings.
 * @param {Object} dictLocale - Localization dictionary.
 * @returns {Object} Configured JustValidate instance.
 */
export default function validator(form, globalConfig = {}, dictLocale) {
    const defaultConfig = { errorFieldCssClass: ['input-error'] };
    const jValidator = new JustValidate(form, { ...defaultConfig, ...globalConfig }, dictLocale);

    return Object.assign(jValidator, {
        /**
         * Applies validation rules to the JustValidate instance.
         * @param {Array} rules - Array of validation rule objects.
         */
        setValidationRules(rules) {
            rules.forEach((rule) => jValidator.addField(rule.element, helper.prepareJRules(rule)));
        },

        /**
         * Creates and applies validation rules from field configuration.
         * @param {Object} fieldsConfig - Field validation configuration.
         */
        createValidationRulesForFieldConfig(fieldsConfig) {
            this.setValidationRules(prepareValidationRules(form, fieldsConfig));
        },
    });
}
