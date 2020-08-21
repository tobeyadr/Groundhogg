import React, {useEffect} from 'react';
import {Container} from "react-bootstrap";
import PropType from "prop-types";
import {connect} from "react-redux";
import {changeSelectedNav} from "../../../actions/reportNavBarActions";
import  {Row,Col} from "react-bootstrap";
import LineChart from "../LineChart/LineChart";
import CustomizedTables from "../CustomizedTable/CustomizedTables";
import Stats from "../Stats/Stats";




export class Report extends React.Component {
    constructor(props) {
        super(props);
    }

    render() {
        switch (this.props.type) {
            case 'table' :
                return (<CustomizedTables classes={this.props.classes} id={this.props.id} start = {this.props.start} end = {this.props.end}  />);
            case 'line-chart' :
                return (<LineChart classes={this.props.classes} id={this.props.id} start = {this.props.start} end = {this.props.end}/>);
            case 'stats' :
                return (<Stats classes={this.props.classes} id={this.props.id} start = {this.props.start} end = {this.props.end}/>);

            // case  'pie' :
            //     return  (<PieChart classes={this.props.classes} id={this.props.id} start = {this.props.start} end = {this.props.end}/>);

            default:

                var stats = {
                    label: "Posts",
                    value: "2,390",
                    percentage: "4.7%",
                    increase: true,
                    chartLabels: [null, null, null, null, null, null, null],
                    attrs: { md: "6", sm: "6" },
                    datasets: [
                        {
                            label: "Today",
                            fill: "start",
                            borderWidth: 1.5,
                            backgroundColor: "rgba(0, 184, 216, 0.1)",
                            borderColor: "rgb(0, 184, 216)",
                            data: [1, 2, 1, 3, 5, 4, 7]
                        }
                    ]
                };
                return  (<h1> default case</h1>);

                // return  <SmallStats
                //     id={`small-stats-1`}
                //     variation="1"
                //     chartData={stats.datasets}
                //     chartLabels={stats.chartLabels}
                //     label={stats.label}
                //     value={stats.value}
                //     percentage={stats.percentage}
                //     increase={stats.increase}
                //     decrease={stats.decrease}
                // />

        }
    }
}


export class ReportRows extends React.Component {
    constructor(props) {
        super(props);
    }

    render() {

        const  col  = {
            marginBottom: 15
        };
        const {classes} = this.props;
        if (this.props.row instanceof Array) {
            return (
                <Row >
                    {this.props.row.map((objects) =>
                        <ReportRows  row={objects} />)}
                </Row>
            );
        } else {
            return (
                <Col
                    lg={this.props.row.lg ? this.props.row.lg : 6}
                    md={this.props.row.md ? this.props.row.md : 12}
                    sm={this.props.row.sm ? this.props.row.sm : 12}
                    style = {col}
                >
                    <Report classes={classes} key={this.props.row.id} id={this.props.row.id} type={this.props.row.type}/>
                </Col>
            );
        }
    }
}


export class Pages extends React.Component {

    componentDidMount() {
        this.props.changeSelectedNav(this.props.navBar.pageSelected);
    }

    render() {

        if (
            this.props.navBar.hasOwnProperty("pages") &&
            this.props.navBar.pages.hasOwnProperty("rows")
        ) {
            return (
                <Container fluid style={{paddingTop : 15}}>
                    {this.props.navBar.pages.rows.map((row) => {
                        // return (<div>{this.handleRow(row)}</div>);
                        return <ReportRows row={row}/>
                    })}
                </Container>
            );
        } else {
            return <h1> loading... </h1>;
        }


    }
}
//
// const Pages = (props) => {
//
//     useEffect(() => {
//         changeSelectedNav(props.navBar.pageSelected);
//     });
//     if (
//
//         props.page.hasOwnProperty("reports") &&
//         props.page.reports.hasOwnProperty("rows")
//     ) {
//         return (
//             <Container fluid>
//                 {props.page.reports.rows.map((row) => {
//                     // return (<div>{this.handleRow(row)}</div>);
//                     return <ReportRows row={row}/>
//                 })}
//             </Container>
//         );
//     } else {
//         return <h1> loading... </h1>;
//     }
//
// }


const mapStateToProps = (state) => {
    return {
        navBar: state.reportNavBar
    };
};


export default connect(mapStateToProps, {changeSelectedNav})(Pages);




