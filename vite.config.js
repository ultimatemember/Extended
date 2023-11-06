import { defineConfig } from 'vite'
import { externalizeDeps } from 'vite-plugin-externalize-deps'

import pluginRewriteAll from 'vite-plugin-rewrite-all';
import react from '@vitejs/plugin-react'
import vue from '@vitejs/plugin-vue'
import vuetify from './app/vue/plugins/vuetify'
import path from 'path'
import * as dotenv from 'dotenv'
import fs from 'fs'
import postcssRTLCSS from 'postcss-rtlcss'
import mkcert from 'vite-plugin-mkcert'
// Convert JSON to PHP.
import jsonToPhp from './build/um-extended-rollup-plugin-json-to-php'

// Vue settings pages.
const getPages = () => {
	return {
		settings: './app/vue/settings/main.js',
	}
}

const getInputs = () => {
	
	return {
		...getPages(),
	}
}


export default() =>  {

	dotenv.config({ path: './build/.env.live', override: true })

	if (fs.existsSync(`./build/.env.live`)) {
		dotenv.config({ path: `./build/.env.live`, override: true })
	}

  return defineConfig({
   		plugins: [
			vue({
				template: {
					compilerOptions: {
					  isCustomElement: (tag) => {
						 return tag.startsWith('v-') 
					  }
					}
				  }
			}), 
			mkcert(), 
			pluginRewriteAll(), 
			react({
				// Add this line
				include: "./app/vue/**/*.vue",
			}),
		 	vuetify({ styles: 'sass', autoImport: true,}), // Enabled by default
		 ],
   		base    : '',
		envDir  : './build',
		build   : {
			// minify            : false, // Uncomment this for debugging production builds.
			// sourcemap         : true, // Uncomment this for debugging production builds.
			assetsInlineLimit : 0, // We need to disable this as it converts small images to base64 inline, but that breaks our inline image function that we use to dynamically set the image url.
			manifest          : true, // We use a manifest to load our files inside of WordPress.
			outDir            : `dist/`, // This is where we put the assets for the current build. Version is either 'Lite' or 'Pro'.
			assetsDir         : '',
			rollupOptions     : {
						input  : getInputs(),
						output : {
							dir            : `dist/assets/`,
							assetFileNames : assetInfo => {
								const images = [
									'.png',
									'.jpg',
									'.jpeg',
									'.gif'
								]

								if (images.includes(path.extname(assetInfo.name))) {
									return 'images/[name].[hash][extname]'
								}

								return '[ext]/[name].[hash][extname]'
							},
							chunkFileNames : 'js/[name].[hash].js',
						},
						plugins : [
							jsonToPhp([
								{
									from : `dist/assets/manifest.json`,
									to   : `dist/manifest.php`
								}
							])
						]
		},
		optimizeDeps : {
			force   : true,
			include : [
			],
			exclude : [
				'@/vue/plugins/constants'
			]
		},
		server : {
			https      : getHttps(),
			cors       : true,
			strictPort : true,
			port       : process.env.VITE_UM_DEV_PORT,
			host       : process.env.VITE_UM_DOMAIN,
			hmr        : {
				port : process.env.VITE_UM_DEV_PORT,
				host : process.env.VITE_UM_DOMAIN
			},
			watch: {
				usePolling: true
			  }
		},
		resolve : {
			alias : [
				{
					find        : '@',
					replacement : path.resolve(__dirname, 'app')
				},
				{
					find        : 'vue',
					replacement : '@vue/compat'
				}
			],
			extensions : [
				'.mjs',
				'.js',
				'.ts',
				'.jsx',
				'.tsx',
				'.json',
				'.vue'
			]
		},
		css : {
			postcss : {
				plugins : [
					postcssRTLCSS()
				]
			},
			preprocessorOptions : {
				scss : {
					additionalData : [
						'@import "./app/assets/scss/variables.scss";',
						'@import "./app/assets/scss/mixins.scss";',
						'@import "./app/assets/scss/vuetify.scss";',
						''
					].join('\n')
				}
			}
		},
		experimental : {
			renderBuiltUrl : (filename, { hostType }) => {
				return 'js' === hostType
					? { runtime: `window.__UMExtendedDynamicImportPreload__(${JSON.stringify(filename)})` }
					: { relative: true }
			}
		}
    }
  });
}


const getHttps = () => {
	let https = false
	if (process.env.VITE_UM_HTTP) {
		return false
	}

	try {
		// Generate a certificate using: `um-extended.local` in the build/ directory.
		if (fs.existsSync('./build/key.pem')) {
			https = {
				key  : fs.readFileSync('./build/key.pem'),
				cert : fs.readFileSync('./build/certificate.pem'),
				ca   : fs.readFileSync(process.env.CRT_ROOT_CA)
			}
		}
	} catch (error) {
		console.log(error)
	}
		
	return https
}
