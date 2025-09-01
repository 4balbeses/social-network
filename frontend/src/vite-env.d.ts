/// <reference types="vite/client" />

declare module '*.css' {
  const content: string;
  export default content;
}

declare module '*.scss' {
  const content: string;
  export default content;
}

interface ImportMetaEnv {
  readonly VITE_API_BASE_URL?: string;
  // add more environment variables as needed
}

interface ImportMeta {
  readonly env: ImportMetaEnv;
}