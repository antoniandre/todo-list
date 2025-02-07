import globals from 'globals'
import pluginJs from '@eslint/js'
import pluginVue from 'eslint-plugin-vue'
import standard from 'eslint-config-standard'
import importPlugin from 'eslint-plugin-import'
import nPlugin from 'eslint-plugin-n'
import promisePlugin from 'eslint-plugin-promise'

export default [
  { files: ['**/*.{js,mjs,vue}'] },
  { languageOptions: { globals: { ...globals.browser, ...globals.node } } },
  pluginJs.configs.recommended,
  {
    name: 'standard',
    rules: standard.rules,
    plugins: {
      import: importPlugin,
      n: nPlugin,
      promise: promisePlugin
    }
  },
  ...pluginVue.configs['flat/essential']
]
