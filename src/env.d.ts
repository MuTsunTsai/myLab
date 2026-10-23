/// <reference types="@rsbuild/core/types" />

/** Shiki 語法檔套件的版本，由 rsbuild.config.ts 注入 */
declare const SHIKI_LANGS_VERSION: string;

/** 所有可從 CDN 載入的 Shiki 語言名稱（含別名），由 rsbuild.config.ts 注入 */
declare const SHIKI_LANGS_NAMES: string[];
