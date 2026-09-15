# Akash Visual Layout Builder

WordPress plugin for building and managing pages, posts, layouts, and popups with a visual editor.

## Repository

Source: [github.com/akash5052/Akash-Visual-Layout-Builder](https://github.com/akash5052/Akash-Visual-Layout-Builder)

WordPress.org details (description, install, FAQ, changelog) live in [`readme.txt`](readme.txt).

## Local development

Editor UI source is in `editor/`. Build compiled assets into `assets/build/`:

```bash
cd editor
npm install
npm run build
```

Package a WordPress zip with `./package.sh` (not required on production sites).
