import assert from 'node:assert/strict';
import { readFile } from 'node:fs/promises';
import vm from 'node:vm';

const source = await readFile(new URL('../../assets/frontend.js', import.meta.url), 'utf8');
const navigation = [];
const listeners = {};
const status = { textContent: 'Loading' };
const classes = new Set();
const wrapper = {
  classList: { add: (value) => classes.add(value) },
  querySelector: () => status,
};
const frameWindow = {};
const frame = {
  contentWindow: frameWindow,
  height: '720',
  getAttribute(name) {
    if (name === 'data-studiocount-instance') return 'studiocount-bookings-1';
    if (name === 'data-studiocount-origin') return 'https://www.studiocount.com';
    return null;
  },
  closest: () => wrapper,
};
const context = {
  URL,
  document: {
    querySelectorAll: () => [frame],
  },
  window: {
    addEventListener(type, handler) {
      listeners[type] = handler;
    },
    location: {
      assign(url) {
        navigation.push(url);
      },
    },
  },
};

vm.runInNewContext(source, context, { filename: 'assets/frontend.js' });
assert.equal(typeof listeners.message, 'function', 'registers one message listener');

const exact = (type, payload = {}) => ({
  data: {
    source: 'studiocount-bookings',
    version: 1,
    type,
    instanceId: 'studiocount-bookings-1',
    ...payload,
  },
  origin: 'https://www.studiocount.com',
  source: frameWindow,
});

listeners.message(exact('ready'));
assert.equal(classes.has('studiocount-bookings--ready'), true, 'accepts exact ready message');
assert.equal(status.textContent, '', 'clears the local loading state');

listeners.message(exact('resize', { height: 840 }));
assert.equal(frame.height, '840', 'applies exact bounded integer height');
listeners.message(exact('resize', { height: 12001 }));
assert.equal(frame.height, '840', 'rejects out-of-range height');
listeners.message({ ...exact('resize', { height: 900 }), origin: 'https://attacker.example' });
assert.equal(frame.height, '840', 'rejects the wrong origin');
listeners.message({ ...exact('resize', { height: 900 }), source: {} });
assert.equal(frame.height, '840', 'rejects the wrong frame window');
listeners.message(exact('resize', { height: 900, extra: true }));
assert.equal(frame.height, '840', 'rejects extra message keys');

listeners.message(exact('navigate', {
  url: 'https://checkout.stripe.com/c/pay/cs_test_example#opaque',
}));
assert.deepEqual(navigation, [
  'https://checkout.stripe.com/c/pay/cs_test_example#opaque',
], 'allows exact Stripe hosted Checkout');

listeners.message(exact('navigate', {
  url: 'https://www.studiocount.com/checkout-return#class_checkout_key=opaque',
}));
assert.equal(navigation.length, 2, 'allows exact StudioCount Checkout return');

for (const url of [
  'https://checkout.stripe.com/evil',
  'https://buy.stripe.com/example',
  'https://www.studiocount.com/book/studioone',
  'https://attacker.example/checkout-return',
  'javascript:alert(1)',
]) {
  listeners.message(exact('navigate', { url }));
}
assert.equal(navigation.length, 2, 'rejects every unapproved navigation destination');

console.log('PASS: 11 focused browser-message assertions');
