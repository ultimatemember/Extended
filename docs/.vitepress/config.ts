import fs from 'fs';
import { defineConfig } from 'vitepress';

const removeExtension = (filename: string) => filename.split('.').shift() ?? '';
const kebabToSpaced = (kebab: string) => kebab.split('-').join(' ');
const capitalizeFirstLetter = (name: string) => name.toUpperCase() + name.slice(1);

function titleCase(str) {
    var splitStr = str.toLowerCase().split(' ');
    for (var i = 0; i < splitStr.length; i++) {
        // You do not need to check if i is larger than splitStr length, as your for does that for you
        // Assign it back to the array
        splitStr[i] = splitStr[i].charAt(0).toUpperCase() + splitStr[i].substring(1);     
    }
    // Directly return the joined string
    return splitStr.join(' '); 
 }

const howToTitle = (filename: string) => {
    const noExtension = removeExtension(filename);
    const spaced = kebabToSpaced(noExtension);

    return titleCase(spaced);
}


const extendedItems = fs.readdirSync('./extended')
    .filter(file => file !== 'index.md')
    .sort()
    .map(file => ({
        text: howToTitle(file),
        link: `/extended/${file}`,
    }));

    const developerdItems = fs.readdirSync('./developer')
    .filter(file => file !== 'index.md')
    .sort()
    .map(file => ({
        text: howToTitle(file),
        link: `/developer/${file}`,
    }));

const basePath = '/Extended/';

export default defineConfig({
    base: basePath,
    appearance: 'dark',
    title: 'UM Extended',
    description: 'Some extended features & functionalities of Ultimate Member plugin.',
    lang: 'en-US',
    lastUpdated: true,
    head: [
        ['link', { rel: 'apple-touch-icon', sizes: '180x180', href: `${basePath}favicon-192x192.png`}],
        ['link', { rel: 'icon', type: 'image/png', sizes: '32x32', href: `${basePath}favicon-192x192.png`}],
        ['link', { rel: 'icon', type: 'image/png', sizes: '16x16', href: `${basePath}favicon-192x192.png`}],
        ['link', { rel: 'manifest', href: `${basePath}site.webmanifest`}],
        ['link', { rel: 'shortcut icon', href: `${basePath}favicon.ico`}],
        ['meta', { name: 'theme-color', content: '#787CB5'}],
    ],
    themeConfig: {
        logo: '/favicon-192x192.png',
        algolia: {
            appId: 'I6RXP3ZUR1',
            apiKey: 'd4fe90d3f1d686f71865fec455c3ac59',
            indexName: 'ultimate-member-extended',
        },
        nav: [
            { text: 'Documentation', link: '/installation' },
            { text: 'Changelog', link: 'https://github.com/ultimatemember/Extended'},
        ],
        socialLinks: [
            { icon: 'github', link: 'https://github.com/ultimatemember/Extended' },
            { icon: 'twitter', link: 'https://twitter.com/umplugins' },
        ],
        sidebar: [
            {
                text: 'Getting Started',
                items: [
                    { text: 'Installation', link: '/installation' },
                ],
            },
            {
                text: 'Developer',
                collapsed: true,
                items: [
                   ...developerdItems
                ],
            },
            {
                text: 'Extended',
                collapsed: false,
                items: [
                    ...extendedItems,
                ],
            },
        ],
        footer: {
            message: 'Released under the MIT License.',
            copyright: 'Copyright © Ultimate Member Group Ltd.',
        },
    },
});