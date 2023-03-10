import template from './salty-performance-analysis-overview.html.twig';
const { Component, Mixin } = Shopware;

Component.register('salty-performance-analysis-overview', {
    template,

    inject: [
        'SaltyPerformanceAnalysisService',
    ],

    mixins: [
        Mixin.getByName('sw-inline-snippet')
    ],

    data() {
        return {
            shopwareConfigurationInformation: [],
            serverConfigurationInformation: [],
            mediaConfigurationInformation: [],
            contentConfigurationInformation: [],
            showSearchBar: false,
            
        }
    },

    created() {
        this.createComponent();
    },

    methods: {
        createComponent() {
            this.getShopwareConfigurationInformation();
            this.getServerConfigurationInformation();
            this.getMediaConfigurationInformation();
            this.getContentConfigurationInformation();
        },

        getServerConfigurationInformation() {
            this.SaltyPerformanceAnalysisService.getServerConfigurationInformation().then(response => {
                this.serverConfigurationInformation = response;
            });
        },

        getShopwareConfigurationInformation() {
            this.SaltyPerformanceAnalysisService.getShopwareConfigurationInformation().then(response => {
                this.shopwareConfigurationInformation = response;
            });
        },
        getMediaConfigurationInformation() {
            this.SaltyPerformanceAnalysisService.getMediaConfigurationInformation().then(response => {
                this.mediaConfigurationInformation = response;
            });
        },

        getContentConfigurationInformation(){
            this.SaltyPerformanceAnalysisService.getContentConfigurationInformation().then(response => {
                this.contentConfigurationInformation = response;
            });
        },

    },
});
