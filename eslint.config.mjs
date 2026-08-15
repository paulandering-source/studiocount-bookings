export default [
  {
    files: ['assets/**/*.js', 'blocks/**/*.js'],
    languageOptions: {
      ecmaVersion: 2020,
      sourceType: 'script',
      globals: {
        document: 'readonly',
        fetch: 'readonly',
        FormData: 'readonly',
        URL: 'readonly',
        window: 'readonly',
      },
    },
    rules: {
      curly: ['error', 'all'],
      eqeqeq: ['error', 'always'],
      'no-implicit-globals': 'error',
      'no-shadow': 'error',
      'no-undef': 'error',
      'no-unused-vars': ['error', { args: 'after-used' }],
    },
  },
];
