import template from './salty-performance-media-grid.html.twig';
const { Component, Mixin } = Shopware;

Component.register('salty-performance-media-grid', {
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
            this.getMediaConfigurationInformation();
        },

        getColumns() {
           return [
               {
                   property: 'fileName',
                   label: 'Name',
                   rawData: true,
                   align: 'left',
                   width: '50px',
               },
               {
                    property: 'url',
                    label: 'Path',
                    rawData: false,
                    
               },
               {
                    property: 'productName',
                    label: 'Ordernumbers',
                    rawData: true,
                    primary: true,
                    align: "center",
                    width: '50px',
                },
                {
                    property: 'id',
                    label: 'Media ID'
                },
           ]
        },

        getMediaConfigurationInformation() {
           /*  this.SaltyPerformanceAnalysisService.getServerConfigurationInformation().then(response => {
                this.serverConfigurationInformation = response;
            });
 */
            this.SaltyPerformanceAnalysisService.getMediaConfigurationInformation().then(response => {
                this.mediaConfigurationInformation = response;
            });
        },

        getSnippetName(name, category) {
            const prefix = 'saltyPerformanceAnalysis';
            let value = /* prefix + '.' + category + '.' + */ name;
            return value;
        }

    },
});