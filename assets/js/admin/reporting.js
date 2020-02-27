( function ( reporting, $, nonces ){

    $.extend( reporting, {

        data: {},
        calendar: null,

        init: function (){

            this.initCalendar()

        },

        initCalendar: function (){

            var self = this

            this.calendar = new Calendar( {
                element: $( '#groundhogg-datepicker' ),
                presets: [
                    {
                        label: 'Last 30 days',
                        start: moment().subtract( 29, 'days' ),
                        end: moment(),
                    }, {
                        label: 'This month',
                        start: moment().startOf( 'month' ),
                        end: moment().endOf( 'month' ),
                    }, {
                        label: 'Last month',
                        start: moment().
                            subtract( 1, 'month' ).
                            startOf( 'month' ),
                        end: moment().subtract( 1, 'month' ).endOf( 'month' ),
                    }, {
                        label: 'Last 7 days',
                        start: moment().subtract( 6, 'days' ),
                        end: moment(),
                    }, {
                        label: 'Last 3 months',
                        start: moment( this.latest_date ).
                            subtract( 3, 'month' ).
                            startOf( 'month' ),
                        end: moment( this.latest_date ).
                            subtract( 1, 'month' ).
                            endOf( 'month' ),
                    } ],
                earliest_date: 'January 1, 2006',
                latest_date: moment(),
                start_date: moment().subtract( 29, 'days' ),
                end_date: moment(),
                callback: function (){
                    self.refresh( this )
                },
            } )

            // run it with defaults
            this.calendar.calendarSaveDates()
        },

        refresh: function ( calendar ){

            var self = this

            self.showLoader()

            var start = moment( calendar.start_date ).format( 'LL' ),
                end = moment( calendar.end_date ).format( 'LL' )

            $.ajax( {
                type: 'post',
                url: ajaxurl,
                dataType: 'json',
                data: {
                    action: 'groundhogg_refresh_dashboard_reports',
                    reports: self.reports,
                    start: start,
                    end: end,
                },
                success: function ( json ){

                    self.hideLoader()

                    self.data = json.data.reports

                    self.renderReports();

                },
                failure: function ( response ){

                    alert( 'Unable to retrieve data...' )

                },
            } )

        },

        renderReports: function (){

            for ( var i = 0; i < this.reports.length; i++ ) {

                var report_id = this.reports[i]
                var report_data = this.data[report_id]

                console.log( report_id, report_data )

                this.renderReport( report_id, report_data )

            }

        },

        renderReport: function ( report_id, report_data ){

            var $report = $( '#' + report_id )

            var type = report_data.type;

            switch ( type ) {
                case 'quick_stat':
                    this.renderQuickStartReport( $report, report_data );
                    break;
            }

        },

        renderQuickStartReport: function ( $report, report_data ){

            console.log( report_data )

            $report.find( '.groundhogg-quick-stat-number' ).
                html( report_data.number )
            $report.find( '.groundhogg-quick-stat-previous' ).
                removeClass( 'green red' ).
                addClass( report_data.compare.arrow.color )
            $report.find( '.groundhogg-quick-stat-compare' ).
                html( report_data.compare.text )
            $report.find( '.groundhogg-quick-stat-arrow' ).
                removeClass( 'up down' ).
                addClass( report_data.compare.arrow.direction )
            $report.find( '.groundhogg-quick-stat-prev-percent' ).
                html( report_data.compare.percent )

        },

        showLoader: function (){
            $( '.gh-loader-overlay' ).show()
            $( '.gh-loader' ).show()
        },

        hideLoader: function (){
            $( '.gh-loader-overlay' ).hide()
            $( '.gh-loader' ).hide()
        },

    } )

    $( function (){
        reporting.init()
    } )

} )( GroundhoggReporting, jQuery, groundhogg_nonces )