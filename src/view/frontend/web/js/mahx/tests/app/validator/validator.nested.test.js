import { describe, test, expect, beforeEach } from 'vitest';
import Validator from '../../../app/new_validator';

function setupForm(html) {
  // eslint-disable-next-line no-undef
  document.body.innerHTML = html;
  // eslint-disable-next-line no-undef
  return document.getElementById('myForm');
}

describe('Nested Form Validation', () => {
  let form;

  beforeEach(() => {
    form = setupForm(`
            <form id="myForm">
                <input name="code" type="text" value="purchaseorder" />
                <input id="po_number" name="additionalData[po_number]" type="text" />
            </form>
        `);
  });

  test('fails when nested rules does not match', async () => {
    const validator = Validator({
      form,
      rules: {
        code: 'required',
        'additionalData.po_number': 'required|number',
      },
    });

    expect(await validator.revalidate()).toBe(false);
  });

  test('pass when nested rules matches', async () => {
    // eslint-disable-next-line no-undef
    document.getElementById('po_number').value = '12345';

    const validator = Validator({
      form,
      rules: {
        code: 'required',
        'additionalData.po_number': 'required|number',
      },
    });

    expect(await validator.revalidate()).toBe(true);
  });
});
