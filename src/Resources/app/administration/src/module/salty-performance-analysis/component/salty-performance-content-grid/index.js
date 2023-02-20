import template from './salty-performance-content-grid.html.twig';
const { Component, Mixin } = Shopware;

Component.register('salty-performance-content-grid', {
    template,

    inject: [
        'SaltyPerformanceAnalysisService',
    ],

    mixins: [
        Mixin.getByName('sw-inline-snippet')
    ],

    props: {
        values: {
            type: Array,
            required: true
        },
    },

    metaInfo() {
        return {
            title: this.$createTitle()
        };
    },

    created() {
        this.createComponent();
    },

    computed: {
        gridColumns() {
            return this.getColumns();
        },

        isLoading() {
            return this.values.length === 0;
        }
    },

    methods: {
        createComponent() {
            this.getContentConfigurationInformation();
        },

        getColumns() {
           return [
               {
                   property: 'fileName',
                   label: 'Page',
                   rawData: true,
                   align: 'left',
                   width: '50px',
               },
               {
                    property: 'url',
                    label: 'Src',
                    rawData: false,
                    isUrl: true,
                    
               },
               {
                    property: 'fileName',
                    label: 'Check Mode',
               },
                {
                    property: 'id',
                    label: 'Media ID'
                },
           ]
        },

        getContentConfigurationInformation() {
            this.SaltyPerformanceAnalysisService.getContentConfigurationInformation().then(response => {
                this.contentConfigurationInformation = response;
            });
        },

        getSnippetName(name, category) {
            const prefix = 'saltyPerformanceAnalysis';
            let value = /* prefix + '.' + category + '.' + */ name;
            return value;
        }

    },
});