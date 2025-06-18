You need to migrate specific JavaScript files to be valid TypeScript.

Concretely, you need to migrate the JavaScript code in <file> to TypeScript. There are three possible scenarios:

- If the file is already using the extension `.ts` or `.tsx`, it may have already been renamed from `.js`. You MUST make the changes to migrate the code to TypeScript directly within the file.
- Otherwise, if the file is using the extension `.js` and it contains React components (JSX), you must create a new file of the same name but with the `.tsx` extension and write the code migrated to TypeScript in there.
- Otherwise, if the file is using the extension `.js` and it does not contain any JSX, you must create a new file of the same name but with the `.ts` extension and write the code migrated to TypeScript in there.

First, use the `read_file` tool to collect sufficient relevant context. Some of the files the user may have already provided to you. In that case, DO NOT READ THEM AGAIN.

- Search for `tsconfig.json`, `.eslintrc.json`, and `.eslintrc.js` files in the project root directory. Read all of them that you find, to know which TypeScript and TSDoc configuration requirements the migrated TypeScript code must follow.
- Search the current directory and any parent directories _within_ the project for `types.ts` files. You MUST read all of these files to understand the project-specific types.
- Look for other TypeScript files in the same directory or a sibling directory. Read 1-3 files to learn about the project-specific TypeScript conventions and best practices, e.g. regarding specific type imports, code style, documentation, or file structure.
    - If you are writing a `.ts` file, you must ONLY consider existing `.ts` files for this contextual research.
    - If you are writing a `.tsx` file, you must ONLY consider existing `.tsx` files for this contextual research.

With that context in mind, you MUST follow the following steps in order to migrate the <file> to TypeScript:

1. Migrate the JavaScript code from <file> to TypeScript, following the patterns learned from ALL previous context.
    - NEVER change any names, e.g. of variables or functions.
    - NEVER change any logic.
    - In addition to the TypeScript code itself, you MUST also ensure to update any JavaScript doc blocks to follow TSDoc syntax, according to project configuration.
    - If any usage of the obsolete `PropTypes` (`prop-types`) is present, you MUST remove it. Using TypeScript itself is a sufficient replacement.
2. Read the `package.json` file in the root directory to check which `"scripts"` are available. Identify any relevant lint, build, and format scripts, which you can use to verify whether the TypeScript code you wrote is accurate.
    - To find the relevant lint script, look for names like "lint-js", "lint-ts", or "lint" for example.
    - To find the relevant build script, look for names like "build:dev", or "build" for example.
    - To find the relevant format script, look for names like "format-js", "format-ts", or "format" for example.
    - Remember the exact names of these scripts that you found.
3. If you found a relevant build script, RUN IT in the command line via NPM and check the output. Here is an example how to run it:
    - If the script name is "build", execute `npm run build` in the command line.
4. If the build script you ran reported TypeScript errors, inspect them carefully. Then review the TypeScript you wrote to see how you can fix the errors. Update the previously generated TypeScript code to fix the reported errors.
5. If you found a relevant lint script, RUN IT in the command line via NPM and check the output. Here is an example how to run it:
    - If the script name is "lint-js", execute `npm run lint-js` in the command line.
6. If the lint script you ran reported errors, inspect them carefully.
    - For any Prettier errors, DO NOT try to fix them manually. Instead, run the format script you previously found if one exists.
    - For any errors other than Prettier, review the TypeScript you wrote to see how you can fix the errors. Update the previously generated TypeScript code to fix the reported errors.
7. If you found a relevant build script before, RUN IT again and check the output. Afterwards, YOU MUST STOP.
    - If the scripts no longer report TypeScript errors, all is well.
    - If the scripts still report TypeScript errors, please share the feedback with the user, to let them decide on the next steps.
