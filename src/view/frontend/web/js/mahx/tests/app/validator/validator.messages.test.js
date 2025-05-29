import { describe, test, expect, beforeEach } from 'vitest';
import Validator from '../../../app/new_validator';

function setupForm(html) {
  // eslint-disable-next-line no-undef
  document.body.innerHTML = html;
  // eslint-disable-next-line no-undef
  return document.getElementById('myForm');
}

async function validateAndSubmitForm(data) {
  const validator = new Validator(data);

  const isValid = await validator.revalidate();
  expect(isValid).toBe(false);

  data.form.dispatchEvent(
    new Event('submit', { cancelable: true, bubbles: true })
  );
  // adding small delay in order to give time for justvalidate to apply errors to form elements.
  await new Promise((resolve) => {
    setTimeout(resolve, 10);
  });
}

describe('Validation Messages', () => {
  let form;
  const rules = {
    required: 'required',
    required_if: 'required_if:email,abcd',
    required_unless: 'required_unless:email,xyz',
    required_with: 'required_with:email',
    required_without: 'required_without:non_existing_field',
    required_with_all: 'required_with_all:email,alpha',
    required_without_all: 'required_without_all:non_existing_1,non_existing_2',
    email: 'email',
    uppercase: 'uppercase',
    lowercase: 'lowercase',
    json: 'json',
    alpha: 'alpha',
    numeric: 'numeric',
    alpha_num: 'alpha_num',
    alpha_dash: 'alpha_dash',
    alpha_spaces: 'alpha_spaces',
    in: 'in:yes,no',
    not_in: 'not_in:foo,bar',
    min: 'min:10',
    max: 'max:1',
    between: 'between:3,5',
    digits: 'digits:4',
    digits_between: 'digits_between:3,5',
    url: 'url',
    integer: 'integer',
    boolean: 'boolean',
    array: 'array',
    same: 'same:email',
    regex: 'regex:/^[A-Z]{3}$/',
    date: 'date',
    accepted: 'accepted',
    present: 'present',
    different: 'different:email',
    after: 'after:before',
    before: 'before:after',
  };

  beforeEach(() => {
    form = setupForm(`
        <form id="myForm">
            <label>required: <input name="required" type="text"></label>
            <label>email: <input name="email" type="email" value="abcd"></label>
            <label>alpha: <input name="alpha" type="text" value="abcd123"></label>
            <label>alpha_num: <input name="alpha_num" type="text" value="abcd_123"></label>
            <label>alpha_dash: <input name="alpha_dash" type="text" value="abcd@123"></label>
            <label>numeric: <input name="numeric" type="text" value="abcd"></label>
            <label>integer: <input name="integer" type="text" value="abcd"></label>
            <label>boolean: <input name="boolean" type="text" value="abcd"></label>
            <label>in (yes,no): <input name="in" type="text" value="abcd"></label>
            <label>not_in (foo,bar): <input name="not_in" type="text" value="foo"></label>
            <label>different (from \`email\`): <input name="different" type="text" value="abcd"></label>
            <label>accepted (yes/on/1/true): <input name="accepted" type="text" value="abcd"></label>
            <label>after (e.g., 2025-01-01): <input name="after" type="date" value="2024-01-01"></label>
            <label>before (e.g., 2025-12-31): <input name="before" type="date" value="2026-12-31"></label>
            <label>min (min 3 chars): <input name="min" type="number" value="5"></label>
            <label>max (max 5 chars): <input name="max" type="number" value="3"></label>
            <label>between (3-5 chars): <input name="between" type="number" value="10"></label>
            <label>digits (4 digits): <input name="digits" type="text" value="123456"></label>
            <label>regex (e.g., ABC): <input name="regex" type="text" value="abcd"></label>
            <label>url: <input name="url" type="url" value="invalid url"></label>
            <label>date: <input name="date" type="date" value="not a date"></label>
            <label>array (comma-separated values): <input name="array" type="text" value="abcd"></label>
        </form>
        `);
  });

  test('default error messages', async () => {
    const rakitMessages = {
      required: 'The required is required',
      required_if: 'Custom message',
      required_unless: 'The required_unless is required',
      required_with: 'The required_with is required',
      required_without: 'The required_without is required',
      required_with_all: 'The required_with_all is required',
      required_without_all: 'The required_without_all is required',
      email: 'The email is not valid email',
      uppercase: 'The uppercase must be uppercase',
      lowercase: 'The lowercase must be lowercase',
      json: 'The json must be a valid JSON string',
      alpha: 'The alpha only allows alphabet characters',
      numeric: 'The numeric must be numeric',
      alpha_num: 'The alpha_num must only contain letters and numbers',
      alpha_dash:
        'The alpha_dash must only contain letters, numbers, dashes and underscores',
      alpha_spaces: 'The alpha_spaces must only contain letters and spaces',
      in: 'The in only allows yes,no',
      not_in: 'The not_in is not allowing foo,bar',
      min: 'The min minimum is 10',
      max: 'The max maximum is 1',
      between: 'The between must be between 3 and 5',
      digits: 'The digits must be numeric and have an exact length of 4',
      digits_between:
        'The digits_between must have a length between the given 3 and 5',
      url: 'The url is not valid url',
      integer: 'The integer must be an integer',
      boolean: 'The boolean must be a boolean',
      array: 'The array must be an array',
      same: 'The same must be same as email',
      regex: 'The regex is not valid format',
      date: 'The date is not a valid date',
      accepted: 'The accepted must be accepted',
      present: 'The present must be present',
      different: 'The different must be different from email',
      after: 'The after must be a date after before',
      before: 'The before must be a date before after',
    };

    await validateAndSubmitForm({ form, rules });

    Object.keys(rules).forEach((field) => {
      const input = form.elements.namedItem(field);
      if (!input) {
        return;
      }

      const errorEl = input.nextElementSibling;
      const actual = errorEl?.textContent?.trim() || '';
      const expected = rakitMessages[field];

      if (!expected) {
        return;
      }

      expect(actual).toBe(expected);
    });
  });

  test('custom error message', async () => {
    const messages = {
      'required:required': 'Custom message',
      'required_if:required_if': 'Custom message',
      'required_unless:required_unless': 'Custom message',
      'required_with:required_with': 'Custom message',
      'required_without:required_without': 'The Custom message',
      'required_without:required_with_all': 'The Custom message',
      'required_without_all:required_without_all': 'The Custom message',
      'email:email': 'Custom message',
      'uppercase:uppercase': 'Custom message',
      'lowercase:lowercase': 'Custom message',
      'json:json': 'Custom message',
      'alpha:alpha': 'Custom message',
      'numeric:numeric': 'Custom message',
      'alpha_num:alpha_num': 'Custom message',
      'alpha_dash:alpha_dash': 'Custom message',
      'alpha_spaces:alpha_spaces': 'Custom message',
      'in:in': 'Custom message',
      'not_in:not_in': 'Custom message',
      'min:min': 'Custom message',
      'max:max': 'Custom message',
      'between:between': 'Custom message',
      'digits:digits': 'Custom message',
      'digits_between:digits_between': 'Custom message',
      'url:url': 'Custom message',
      'integer:integer': 'Custom message',
      'boolean:boolean': 'Custom message',
      'array:array': 'Custom message',
      'same:same': 'Custom message',
      'regex:regex': 'Custom message',
      'date:date': 'Custom message',
      'accepted:accepted': 'Custom message',
      'present:present': 'Custom message',
      'different:different': 'Custom message',
      'after:after': 'Custom message',
      'before:before': 'Custom message',
    };

    await validateAndSubmitForm({ form, rules, messages });

    Object.keys(rules).forEach((field) => {
      const input = form.elements.namedItem(field);
      if (!input) {
        return;
      }

      const errorEl = input.nextElementSibling;
      const actual = errorEl?.textContent?.trim() || '';

      expect(actual).toBe('Custom message');
    });
  });

  test('aliases', async function () {
    const aliases = {
      required: 'Alias',
      required_if: 'Alias',
      required_unless: 'Alias',
      required_with: 'Alias',
      required_without: 'Alias',
      required_with_all: 'Alias',
      required_without_all: 'Alias',
      email: 'Alias',
      uppercase: 'Alias',
      lowercase: 'Alias',
      json: 'Alias',
      alpha: 'Alias',
      numeric: 'Alias',
      alpha_num: 'Alias',
      alpha_dash: 'Alias',
      alpha_spaces: 'Alias',
      in: 'Alias',
      not_in: 'Alias',
      min: 'Alias',
      max: 'Alias',
      between: 'Alias',
      digits: 'Alias',
      digits_between: 'Alias',
      url: 'Alias',
      integer: 'Alias',
      boolean: 'Alias',
      array: 'Alias',
      same: 'Alias',
      regex: 'Alias',
      date: 'Alias',
      accepted: 'Alias',
      present: 'Alias',
      different: 'Alias',
      after: 'Alias',
      before: 'Alias',
    };

    await validateAndSubmitForm({ form, rules, aliases });

    Object.keys(rules).forEach((field) => {
      const input = form.elements.namedItem(field);
      if (!input) {
        return;
      }

      const errorEl = input.nextElementSibling;
      const actual = errorEl?.textContent?.trim() || '';

      expect(actual).toContain('Alias');
    });
  });
});
