
<template>
	    <div class="single-item">
            <div style="padding:20px 0px 20px">
                <a href="" @click.prevent="$router.back()" style="margin: 10px">&laquo; Go Back</a>
            </div>
            <div v-if="!pageLoading" class="buttons">
                <a href="javascript:;" v-if="showActiveBtn" :class="disableButton ? 'disabled':''" @click.once.prevent="activateExtension( extension.slug)">Activate</a>
                <a href="javascript:;" v-if="showDeactiveBtn" @click.once.prevent="deactivateExtension( extension.slug)">Deactivate</a>
            </div>
            <div class="avatar">
                <VSkeletonLoader :loading="pageLoading" type="avatar" width="90px"  class="mx-auto" >
                    <img v-bind:src="logo(`app/assets/images/icon-128×128.png`)" style="height: 70px;margin-left:10px;margin-right:10px;float:left" /> 
                </VSkeletonLoader>
            </div>
            <VSkeletonLoader v-if="pageLoading" :loading="pageLoading" type="list-item, list-item-two-line" ></VSkeletonLoader>
               
            <div v-if="!pageLoading">
                <h1>{{extension.name}}</h1>
                <span class="version">Version {{extension.version}}</span>  <span style="padding:0px 10px 0px 10px">·</span> 
                <span class="author">By {{extension.author}}</span>
        
                <span class="require">Requires UM {{extension.um_version}}+</span>
                <div class="clear"></div>
            </div>

            <Transition>
                <div v-if="showLoader" class="activate-status">Applying...</div>
            </Transition>
            <div class="description">
                <h4>Description</h4>
                <VSkeletonLoader :loading="pageLoading" type="list-item, list-item-three-line" >
                {{ extension.description  }}
                </VSkeletonLoader>
            </div>
        </div>
</template>

<script>

import axios from 'axios'
import { store } from './Store.js'
import { isProxy, toRaw } from 'vue';

import NProgress from 'nprogress'
import 'nprogress/nprogress.css'

NProgress.configure({ parent: '.um-extended-settings',  showSpinner: true, asing: 'ease', speed: 50,  minimum: 0.6, trickleSpeed: 500  });

export default {
    name: 'UM_Settings',
    data(){ 
        return {
                extension: {},
                showActiveBtn: false,
                showDeactiveBtn: false,
                showLoader: false,
                disableButton: false,
                pageLoading: true,
        }
    },
    mounted() {
      const param = this.$route.params.id;
        var self = this;
        if (isProxy(store.extensions)){
            const extensions = toRaw( store.extensions);
            Object.keys(extensions).forEach((value, index) => {
                if( value == param ) {
                    self.extension =  extensions[value];
                    if( self.extension.is_active == true ) {
                        self.showActiveBtn = false;
                        self.showDeactiveBtn = true;
                    } else{
                        self.showActiveBtn = true;
                        self.showDeactiveBtn = false;
                    }
                    setTimeout(function(){
                        self.pageLoading = false;
                    },300);
                }
            });
            if( Object.keys(extensions).length <= 0 ){
                this.load_extension(param);
            }
        } 
    },
    methods: {
        logo(img) {
            return um_extended.plugin_url + '' + img
        },
        sleeper(ms) {
            return function(x) {
                return new Promise(resolve => setTimeout(() => resolve(x), ms));
            };
        },
        async load_extension(extension_slug) {
            var self = this;

            let form_data = new FormData;
            form_data.append('action', 'um_extended_extensions');
            form_data.append('extension', extension_slug );

           return await axios.post(
                um_extended.ajax_url, 
                form_data,
            ).then((response) => {
                self.extension =  response.data.data;
                if( self.extension.is_active == true ) {
                        self.showActiveBtn = false;
                        self.showDeactiveBtn = true;
                } else{
                    self.showActiveBtn = true;
                    self.showDeactiveBtn = false;
                }
                setTimeout(function(){
                    self.pageLoading = false;
                },300);
            })
        },
        async activateExtension( extension_slug ) {
            var self = this;
            let form_data = new FormData;
            form_data.append('action', 'um_extended_activate');
            form_data.append('extension', extension_slug );
            NProgress.inc(0.5)
            self.showLoader = true;
            self.disableButton = true;
            return await axios.post(
                um_extended.ajax_url, 
                form_data,
            ).then(this.sleeper(300)).
            then( () => {
                NProgress.inc(1.0) 
            }).then((response) => {
                self.showActiveBtn = false;
                self.showDeactiveBtn = true;
                self.showLoader = false;
                self.disableButton = false;
                NProgress.done()
                NProgress.remove()
                
            })
        },
        async deactivateExtension( extension_slug ) {
            var self = this;
            let form_data = new FormData;
            form_data.append('action', 'um_extended_deactivate');
            form_data.append('extension', extension_slug );
            self.showLoader = true;
            return await axios.post(
                um_extended.ajax_url, 
                form_data,
            ).then((response) => {
                self.showActiveBtn = true;
                self.showDeactiveBtn = false;
                self.showLoader = false;
            })
        }
        
  },
}
</script>
<style>
   @import '../../assets/scss/settings.scss'; 
</style>

