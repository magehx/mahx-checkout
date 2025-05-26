import { describe, test, expect, beforeEach } from 'vitest';
import Validator from '../../app/new_validator.js';

function setupForm(html) {
  document.body.innerHTML = html;
  return document.getElementById('myForm');
}

describe('ValidatorTest', () => {
  describe('rule:required', () => {
    let form;

    beforeEach(() => {
      form = setupForm(`
        <form id="myForm">
          <input name="name" type="text" />
          <input name="email" type="email" />
        </form>
      `);
    });

    test('failure', async () => {
      const validator = Validator({
        form,
        rules: { name: 'required', email: 'required' },
      });
      expect(await validator.revalidate()).toBe(false);
    });

    test('pass', async () => {
      form.elements.name.value = 'Joe';
      form.elements.email.value = 'joedoe@example.com';

      const validator = Validator({
        form,
        rules: { name: 'required', email: 'required' },
      });
      expect(await validator.revalidate()).toBe(true);
    });
  });

  describe('rule:required_if', () => {
    let form;

    beforeEach(() => {
      form = setupForm(`
        <form id="myForm">
          <select name="foundOn">
            <option value="linkedin">LinkedIn</option>
            <option value="facebook">Facebook</option>
            <option value="other">Other</option>
          </select>
          <input name="foundOnOther" type="text" />
        </form>
      `);
    });

    test('failure', async () => {
      form.elements.foundOn.value = 'other';

      const validator = Validator({
        form,
        rules: { foundOnOther: 'required_if:foundOn,other' },
      });
      expect(await validator.revalidate()).toBe(false);
    });

    test('pass', async () => {
      form.elements.foundOn.value = 'other';
      form.elements.foundOnOther.value = 'Google';

      const validator = Validator({
        form,
        rules: { foundOnOther: 'required_if:foundOn,other' },
      });
      expect(await validator.revalidate()).toBe(true);
    });
  });

  describe('rule:required_unless', () => {
    let form;

    beforeEach(() => {
      form = setupForm(`
        <form id="myForm">
          <select name="account_type">
            <option value="">-- Select --</option>
            <option value="individual">Individual</option>
            <option value="business">Business</option>
          </select>
          <input type="text" name="company_name" />
        </form>
      `);
    });

    test('failure', async () => {
      form.elements.account_type.value = 'business';

      const validator = Validator({
        form,
        rules: { company_name: 'required_unless:account_type,individual' },
      });
      expect(await validator.revalidate()).toBe(false);
    });

    test('pass', async () => {
      form.elements.account_type.value = 'business';
      form.elements.company_name.value = 'My Company';

      const validator = Validator({
        form,
        rules: { company_name: 'required_unless:account_type,individual' },
      });
      expect(await validator.revalidate()).toBe(true);
    });
  });

  describe('rule:required_with', () => {
    let form;

    beforeEach(() => {
      form = setupForm(`
        <form id="myForm">
          <input type="email" name="email" />
          <input type="email" name="confirm_email" />
        </form>
      `);
    });

    test('failure', async () => {
      form.elements.email.value = 'joedoe@example.com';

      const validator = Validator({
        form,
        rules: { confirm_email: 'required_with:email' },
      });
      expect(await validator.revalidate()).toBe(false);
    });

    test('pass', async () => {
      form.elements.email.value = 'joedoe@example.com';
      form.elements.confirm_email.value = 'joedoe@example.com';

      const validator = Validator({
        form,
        rules: { confirm_email: 'required_with:email' },
      });
      expect(await validator.revalidate()).toBe(true);
    });
  });

  describe('rule:required_without', () => {
    let form;

    beforeEach(() => {
      form = setupForm(`
        <form id="myForm">
          <input type="email" name="email" />
          <input type="text" name="phone" />
        </form>
      `);
    });

    test('failure', async () => {
      const validator = Validator({
        form,
        rules: { phone: 'required_without:email' },
      });
      expect(await validator.revalidate()).toBe(false);
    });

    test('pass', async () => {
      form.elements.email.value = 'joedoe@example.com';

      const validator = Validator({
        form,
        rules: { phone: 'required_without:email' },
      });
      expect(await validator.revalidate()).toBe(true);
    });
  });

  describe('rule:email', () => {
    let form;

    beforeEach(() => {
      form = setupForm(`
        <form id="myForm">
          <input type="email" name="email" />
        </form>
      `);
    });

    test('failure', async () => {
      form.elements.email.value = 'joe';

      const validator = Validator({
        form,
        rules: { email: 'email' },
      });
      expect(await validator.revalidate()).toBe(false);
    });

    test('pass', async () => {
      form.elements.email.value = 'joedoe@example.com';

      const validator = Validator({
        form,
        rules: { email: 'email' },
      });
      expect(await validator.revalidate()).toBe(true);
    });
  });

  describe('rule:uppercase', () => {
    let form;

    beforeEach(() => {
      form = setupForm(`
        <form id="myForm">
          <input name="promo_code" type="text" />
        </form>
      `);
    });

    test('failure', async () => {
      form.elements.promo_code.value = 'discount20';

      const validator = Validator({
        form,
        rules: { promo_code: 'uppercase' },
      });
      expect(await validator.revalidate()).toBe(false);
    });

    test('pass', async () => {
      form.elements.promo_code.value = 'DISCOUNT20';

      const validator = Validator({
        form,
        rules: { promo_code: 'uppercase' },
      });
      expect(await validator.revalidate()).toBe(true);
    });
  });

  describe('rule:lowercase', () => {
    let form;

    beforeEach(() => {
      form = setupForm(`
        <form id="myForm">
          <input name="promo_code" type="text" />
        </form>
      `);
    });

    test('failure', async () => {
      form.elements.promo_code.value = 'Discount20';

      const validator = Validator({
        form,
        rules: { promo_code: 'lowercase' },
      });
      expect(await validator.revalidate()).toBe(false);
    });

    test('pass', async () => {
      form.elements.promo_code.value = 'discount20';

      const validator = Validator({
        form,
        rules: { promo_code: 'lowercase' },
      });
      expect(await validator.revalidate()).toBe(true);
    });
  });

    describe('rule:json', () => {
    let form;

    beforeEach(() => {
      form = setupForm(`
        <form id="myForm">
          <textarea name="config"></textarea>
        </form>
      `);
    });

    test('failure', async () => {
      form.elements.config.value = 'not a json value';

      const validator = Validator({
        form,
        rules: { config: 'json' },
      });

      expect(await validator.revalidate()).toBe(false);
    });

    test('pass', async () => {
      form.elements.config.value = '{"some": "valid json"}';

      const validator = Validator({
        form,
        rules: { config: 'json' },
      });

      expect(await validator.revalidate()).toBe(true);
    });
  });

  describe('rule:alpha', () => {
    let form;

    beforeEach(() => {
      form = setupForm(`
        <form id="myForm">
          <input name="firstname" type="text" />
        </form>
      `);
    });

    test('failure', async () => {
      form.elements.firstname.value = 'Joe 1 Doe 123';

      const validator = Validator({
        form,
        rules: { firstname: 'alpha' },
      });

      expect(await validator.revalidate()).toBe(false);
    });

    test('pass', async () => {
      form.elements.firstname.value = 'JoeDoe';

      const validator = Validator({
        form,
        rules: { firstname: 'alpha' },
      });

      expect(await validator.revalidate()).toBe(true);
    });
  });

  describe('rule:numeric', () => {
    let form;

    beforeEach(() => {
      form = setupForm(`
        <form id="myForm">
          <input name="age" type="text" />
        </form>
      `);
    });

    test('failure', async () => {
      form.elements.age.value = '12 years';

      const validator = Validator({
        form,
        rules: { age: 'numeric' },
      });

      expect(await validator.revalidate()).toBe(false);
    });

    test('pass', async () => {
      form.elements.age.value = '12';

      const validator = Validator({
        form,
        rules: { age: 'numeric' },
      });

      expect(await validator.revalidate()).toBe(true);
    });
  });

  describe('rule:alpha_num', () => {
    let form;

    beforeEach(() => {
      form = setupForm(`
        <form id="myForm">
          <input name="password" type="text" />
        </form>
      `);
    });

    test('failure', async () => {
      form.elements.password.value = 'test^&123';

      const validator = Validator({
        form,
        rules: { password: 'alpha_num' },
      });

      expect(await validator.revalidate()).toBe(false);
    });

    test('pass', async () => {
      form.elements.password.value = 'test123456';

      const validator = Validator({
        form,
        rules: { password: 'alpha_num' },
      });

      expect(await validator.revalidate()).toBe(true);
    });
  });

  describe('rule:alpha_dash', () => {
    let form;

    beforeEach(() => {
      form = setupForm(`
        <form id="myForm">
          <input name="password" type="text" />
        </form>
      `);
    });

    test('failure', async () => {
      form.elements.password.value = 'test^&123_-';

      const validator = Validator({
        form,
        rules: { password: 'alpha_dash' },
      });

      expect(await validator.revalidate()).toBe(false);
    });

    test('pass', async () => {
      form.elements.password.value = 'test---123456___';

      const validator = Validator({
        form,
        rules: { password: 'alpha_dash' },
      });

      expect(await validator.revalidate()).toBe(true);
    });
  });

  describe('rule:alpha_spaces', () => {
    let form;

    beforeEach(() => {
      form = setupForm(`
        <form id="myForm">
          <input name="password" type="text" />
        </form>
      `);
    });

    test('failure', async () => {
      form.elements.password.value = 'test^&123_-';

      const validator = Validator({
        form,
        rules: { password: 'alpha_spaces' },
      });

      expect(await validator.revalidate()).toBe(false);
    });

    test('pass', async () => {
      form.elements.password.value = ' Joe Doe Khureshi ';

      const validator = Validator({
        form,
        rules: { password: 'alpha_spaces' },
      });

      expect(await validator.revalidate()).toBe(true);
    });
  });

  describe('rule:in', () => {
    let form;

    beforeEach(() => {
      form = setupForm(`
        <form id="myForm">
          <select name="role">
            <option value="">-- Select --</option>
            <option value="admin">Admin</option>
            <option value="editor">Editor</option>
            <option value="viewer">Viewer</option>
          </select>
        </form>
      `);
    });

    test('failure', async () => {
      form.elements.role.value = 'another';

      const validator = Validator({
        form,
        rules: { role: 'in:admin,editor,viewer' },
      });

      expect(await validator.revalidate()).toBe(false);
    });

    test('pass', async () => {
      form.elements.role.value = 'editor';

      const validator = Validator({
        form,
        rules: { role: 'in:admin,editor,viewer' },
      });

      expect(await validator.revalidate()).toBe(true);
    });
  });

  describe('rule:not_in', () => {
    let form;

    beforeEach(() => {
      form = setupForm(`
        <form id="myForm">
          <input name="username" type="text" />
        </form>
      `);
    });

    test('failure', async () => {
      form.elements.username.value = 'admin';

      const validator = Validator({
        form,
        rules: { username: 'not_in:admin,root' },
      });

      expect(await validator.revalidate()).toBe(false);
    });

    test('pass', async () => {
      form.elements.username.value = 'johndoe';

      const validator = Validator({
        form,
        rules: { username: 'not_in:admin,root' },
      });

      expect(await validator.revalidate()).toBe(true);
    });
  });

  describe('rule:min|max|between', () => {
  let form;

  describe('text inputs (string length)', () => {
    beforeEach(() => {
      form = setupForm(`
        <form id="myForm">
          <input name="code" type="text" />
        </form>
      `);
    });

    test('min failure for short string', async () => {
      form.elements.code.value = '5'; // 1 character

      const validator = Validator({
        form,
        rules: { code: 'min:2' }, // expects at least 2 characters
      });

      expect(await validator.revalidate()).toBe(false);
    });

    test('max failure for long string', async () => {
      form.elements.code.value = '123456'; // 6 characters

      const validator = Validator({
        form,
        rules: { code: 'max:5' }, // allows max 5 characters
      });

      expect(await validator.revalidate()).toBe(false);
    });

    test('between pass for acceptable length', async () => {
      form.elements.code.value = '123'; // 3 characters

      const validator = Validator({
        form,
        rules: { code: 'between:2,5' }, // between 2 and 5 characters
      });

      expect(await validator.revalidate()).toBe(true);
    });
  });

  describe('number inputs (numeric value)', () => {
    beforeEach(() => {
      form = setupForm(`
        <form id="myForm">
          <input name="age" type="number" id="age" />
          <input name="firstname" type="text" />
        </form>
      `);
    });

    test('min failure for too small number', async () => {
      form.elements.age.value = '1';

      const validator = Validator({
        form,
        rules: { age: 'min:2' }, // expects age >= 2
      });

      expect(await validator.revalidate()).toBe(false);
    });

    test('max failure for too large number', async () => {
      form.elements.age.value = '10';

      const validator = Validator({
        form,
        rules: { age: 'max:5' }, // expects age <= 5
      });

      expect(await validator.revalidate()).toBe(false);
    });

    test('between pass for number in range', async () => {
      form.elements.age.value = '3';

      const validator = Validator({
        form,
        rules: { age: 'between:2,5' }, // between 2 and 5
      });

      expect(await validator.revalidate()).toBe(true);
    });
  });
});


  describe('rule:digits|digits_between', () => {
    let form;

    beforeEach(() => {
      form = setupForm(`
        <form id="myForm">
          <input name="otp" type="text" />
        </form>
      `);
    });

    test('digits failure', async () => {
      form.elements.otp.value = '1234a';

      const validator = Validator({
        form,
        rules: { otp: 'digits:5' },
      });

      expect(await validator.revalidate()).toBe(false);
    });

    test('digits_between pass', async () => {
      form.elements.otp.value = '123456';

      const validator = Validator({
        form,
        rules: { otp: 'digits_between:5,6' },
      });

      expect(await validator.revalidate()).toBe(true);
    });
  });

  describe('rule:url|integer|boolean|same|regex|date|accepted|present|different|after|before|nullable', () => {
    let form;

    beforeEach(() => {
      form = setupForm(`
        <form id="myForm">
          <input name="website" type="text" />
          <input name="age" type="text" />
          <input name="tos" type="checkbox" value="1" />
          <input name="confirm_age" type="text" />
          <input name="start_date" type="text" />
          <input name="end_date" type="text" />
        </form>
      `);
    });

    test('url', async () => {
      form.elements.website.value = 'http://example.com';
      const validator = Validator({ form, rules: { website: 'url' } });
      expect(await validator.revalidate()).toBe(true);
    });

    test('integer', async () => {
      form.elements.age.value = '42';
      const validator = Validator({ form, rules: { age: 'integer' } });
      expect(await validator.revalidate()).toBe(true);
    });

    test('boolean', async () => {
      form.elements.age.value = 'true';
      const validator = Validator({ form, rules: { age: 'boolean' } });
      expect(await validator.revalidate()).toBe(true);
    });

    test('same', async () => {
      form.elements.age.value = '25';
      form.elements.confirm_age.value = '25';
      const validator = Validator({ form, rules: { confirm_age: 'same:age' } });
      expect(await validator.revalidate()).toBe(true);
    });

    test('regex', async () => {
      form.elements.age.value = 'abc123';
      const validator = Validator({ form, rules: { age: 'regex:^[a-z0-9]+$' } });
      expect(await validator.revalidate()).toBe(true);
    });

    test('date', async () => {
      form.elements.age.value = '2023-05-01';
      const validator = Validator({ form, rules: { age: 'date' } });
      expect(await validator.revalidate()).toBe(true);
    });

    test('accepted', async () => {
      form.elements.tos.checked = true;
      const validator = Validator({ form, rules: { tos: 'accepted' } });
      expect(await validator.revalidate()).toBe(true);
    });

    test('present', async () => {
      const validator = Validator({ form, rules: { age: 'present' } });
      expect(await validator.revalidate()).toBe(true);
    });

    test('different', async () => {
      form.elements.age.value = '25';
      form.elements.confirm_age.value = '30';
      const validator = Validator({ form, rules: { confirm_age: 'different:age' } });
      expect(await validator.revalidate()).toBe(true);
    });

    test('after', async () => {
      form.elements.start_date.value = '2024-01-01';
      form.elements.end_date.value = '2024-02-01';
      const validator = Validator({ form, rules: { end_date: 'after:start_date' } });
      expect(await validator.revalidate()).toBe(true);
    });

    test('before', async () => {
      form.elements.start_date.value = '2024-01-01';
      form.elements.end_date.value = '2023-01-01';
      const validator = Validator({ form, rules: { end_date: 'before:start_date' } });
      expect(await validator.revalidate()).toBe(true);
    });

    test('nullable (passes empty)', async () => {
      form.elements.age.value = '';
      const validator = Validator({ form, rules: { age: 'nullable|integer' } });
      expect(await validator.revalidate()).toBe(true);
    });
  });
});
