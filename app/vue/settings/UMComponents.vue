
<template>
	<div class="um-extended-settings">
       <img v-bind:src="logo(`app/assets/images/icon-128×128.png`)" style="height: 40px;margin-left:10px;margin-right:10px;float:left" /> 
        <h1 class="title">Ultimate Member - Basic Extensions</h1>
        <div class="search">
            <input type="text" name="search" v-model="searchField" @input="searchExtensions(),pageLoading = true" placeholder="Search for extensions, features & functions..." />
        </div>
       <div class="clear"></div>
       <div class="disable-all">
            Enable All Active
            <vueToggleBtn ref="ToggleButton" @setIsActive="disableAll( $event )"></vueToggleBtn>
        </div>
        <div v-if="showSearchResult" style="padding: 20px 0px 15px;float: right;width: 20%;">
            Search Results: <span>{{Object.keys(active_extensions).length + Object.keys(extensions).length}}</span> out of {{ getTotalExtensionsCount() }} found.
        </div>
        <div  v-if="Object.keys(active_extensions).length > 0 && has_active"  :class="'activated-ext ' + ( all_active_enabled ? 'disabled': '' ) " >
            <h2 style="display: inline-block;">Active</h2>
          
            <div v-for="( item, index ) in active_extensions">
                <div  v-if="pageLoading" class="actions-template-card">
                <VSkeletonLoader :loading="pageLoading" type="list-item, list-item-two-line, list-item-three-line" >
                    <div class="details"></div>
                    <div class="buttons"></div>
                    <div class="actions"></div>
                </VSkeletonLoader>
                </div>
                <div v-if="!pageLoading" class="actions-template-card">
                        <div class="details">
                                <h3><a :href="'#/ext/' + item.slug ">{{item.name.replace(/(.{74})..+/, "$1&hellip;")}}</a></h3>
                                <p>{{item.description.replace(/(.{120})..+/, "$1&hellip;")}}</p>
                        </div>
                        <div class="buttons">
                            <a :href="'#/ext/' + item.slug " class="link">Manage Settings</a>
                        </div>
                        <div class="actions">
                            <img v-bind:src="logo(`app/assets/images/icon-128×128.png`)" style="height: 20px;float: left;margin-right: 5px" /> 
                            <span v-if="item.um_package" class="package">{{item.um_package}}</span> 
                            <span v-if="item.doc_url!='Doc URL'" class="sep-dot">·</span>
                            <a v-if="item.doc_url!='Doc URL'" :href="item.doc_url" class="doc-link">Documentation</a>
                            <span class="require">Requires UM v{{item.um_version}}+</span>
                        </div>
                </div>
            </div>
            <div class="clear"></div>
        </div>
        <div v-if="has_active"  class="sep"></div>
        <div v-if="Object.keys(extensions).length > 0" class="featured-ext" >
            <h2>Core</h2>
            <div v-for="( item, index ) in extensions">
                <div  v-if="pageLoading" class="actions-template-card">
                <VSkeletonLoader :loading="pageLoading" type="list-item, list-item-two-line, list-item-three-line" >
                    <div class="details"></div>
                    <div class="buttons"></div>
                    <div class="actions"></div>
                </VSkeletonLoader>
                </div>
                <div v-if="!pageLoading" class="actions-template-card">
                    <div class="details">
                        <h3><a :href="'#/ext/' + item.slug ">{{item.name.replace(/(.{74})..+/, "$1&hellip;")}}</a></h3>
                        <p>{{item.description.replace(/(.{120})..+/, "$1&hellip;")}}</p>
                    </div>
                    <div class="buttons">
                        <a :href="'#/ext/' + item.slug " class="learn-more">Learn more</a>
                    </div>
                    <div class="actions">
                        <img v-bind:src="logo(`app/assets/images/icon-128×128.png`)" style="height: 20px;float: left;margin-right: 5px" /> 
                        <span v-if="item.um_package" class="package">{{item.um_package}}</span> 
                        <span v-if="item.doc_url!='Doc URL'" class="sep-dot">·</span>
                        <a v-if="item.doc_url!='Doc URL'" :href="item.doc_url" class="doc-link">Documentation</a>
                         <span class="require">Requires UM v{{item.um_version}}+</span>
                    </div>
                </div>
            </div>
        </div>
       
        <div class="clear"></div>
	</div>
</template>

<script>
import { isProxy, toRaw } from 'vue';
import axios from 'axios'
import { store } from './Store.js'
import VueToggleBtn from '../plugins/Switch.vue';
import debounce from 'lodash.debounce'


export default {
    name: 'UM_Settings',
    data(){ 
        return {
            extensions: [],
            active_extensions: [],
            has_active: false,
            all_active_enabled: true,
            pageLoading: true,
            searchField: '',
            searchExtensionsTemp: {},
            showSearchResult: false,
        }
    },
    components: {
        VueToggleBtn
    },
   
    created() {
      this.load_extensions();
    },
   
    methods: {
        logo(img) {
            return um_extended.plugin_url + '' + img
        },
        async load_extensions () {

            const search_param = this.$route.params.keyword;
     
            var self = this;

            let form_data = new FormData;
            form_data.append('action', 'um_extended_extensions');

           return await axios.post(
                um_extended.ajax_url,
                form_data
            ).then((response) => {
                self.active_extensions = response.data.data.active_extensions ;
                self.extensions = response.data.data.extensions ;

                self.searchExtensionsTemp.extensions = self.extensions;
                self.searchExtensionsTemp.active_extensions = self.active_extensions;
                
                if( Object.keys( self.active_extensions ).length > 0 ) {
                    this.has_active = true;
                }
                store.extensions = response.data.data.all_extensions ;
                self.$refs.ToggleButton.setDefaultToggleState( response.data.data.all_active_enabled);
                self.all_active_enabled = ! response.data.data.all_active_enabled;
                setTimeout( function(){
                    self.pageLoading = false;
                }, 300 )

                if( search_param != '' ) {
                    self.searchField = search_param;
                    self.searchExtensions();
                }
            })
            
        },
        async disableAll(isActive) {
            var self = this;
           
            let form_data = new FormData;
            form_data.append('action', 'um_extended_disable_all_active');
            form_data.append('state', ! isActive );

           return await axios.post(
                um_extended.ajax_url,
                form_data,
            ).then((response) => {
                self.all_active_enabled = ! isActive;
            })
        },
        searchExtensions: debounce(function() {
            var self = this
            let Extensions = Object.values(toRaw(self.extensions ) )
            let ActiveExtensions = Object.values(toRaw(self.active_extensions ) )
            
             if (self.searchField != '' && self.searchField) {
                self.extensions = Extensions.filter((item) => {
                     return item.name.toUpperCase().includes(self.searchField.toUpperCase())
                })

                self.active_extensions = ActiveExtensions.filter((item) => {
                     return item.name.toUpperCase().includes(self.searchField.toUpperCase())
                })

                self.showSearchResult = true;
                self.$router.push({ path: '/search/' + self.searchField, replace: true })

            } else {
                self.active_extensions = self.searchExtensionsTemp.active_extensions;
                self.extensions =  self.searchExtensionsTemp.extensions;
                self.showSearchResult = false;
                self.$router.push({ path: '/', replace: true })

            }

            self.pageLoading = false
            
        }, 500),
        getTotalExtensionsCount() {
            var self = this;
             return Object.keys( toRaw(self.searchExtensionsTemp.extensions ) ).length + Object.keys( toRaw(self.searchExtensionsTemp.active_extensions ) ).length;
        }
  },
}
</script>
<style>
   @import '../../assets/scss/settings.scss'; 
</style>

