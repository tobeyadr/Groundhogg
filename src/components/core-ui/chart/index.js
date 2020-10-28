/**
 * External dependencies
 */
import React, { useEffect, useState, useRef } from "react";
import { makeStyles } from "@material-ui/core/styles";
import Chartjs from "chart.js";

const lineChartConfig = {
    type: 'line',
    data: {
      labels: ['Red', 'Blue', 'Yellow', 'Green', 'Purple', 'Orange'],
      datasets: [{
          label: '# of Votes',
          data: [12, 19, 3, 5, 2, 3],
          backgroundColor: [
              'rgba(255, 99, 132, 0.2)',
              'rgba(54, 162, 235, 0.2)',
              'rgba(255, 206, 86, 0.2)',
              'rgba(75, 192, 192, 0.2)',
              'rgba(153, 102, 255, 0.2)',
              'rgba(255, 159, 64, 0.2)'
          ],
          borderColor: [
              'rgba(255, 99, 132, 1)',
              'rgba(54, 162, 235, 1)',
              'rgba(255, 206, 86, 1)',
              'rgba(75, 192, 192, 1)',
              'rgba(153, 102, 255, 1)',
              'rgba(255, 159, 64, 1)'
          ],
          borderWidth: 1
      }]
  },
  options: {
    responsive: true,
    maintainAspectRatio: false,
    title: {
      display: true,
      text: '',
      fontFamily:  'Roboto, Helvetica, Arial, sans-serif',
      // fontSize: '18px',
      fontWeight: '100'
    },
    legend: {
      position: 'bottom',
      fontFamily:  'Roboto, Helvetica, Arial, sans-serif'
    },
    tooltips: {
      mode: "index",
      intersect: false,
      backgroundColor: "#FFF",
      bodyFontColor: "#000",
      borderColor: "#727272",
      borderWidth: 2,
      titleFontColor: "#000",
    },
    hover: {
      mode: "nearest",
      intersect: true,
    },
    'scales' : {
      'xAxes' : {
        0 : {
          'type'       : 'time',
          'time'       : {
            'parser'        : "YYY-MM-DD HH:mm:ss",
            'tooltipFormat' : "l HH:mm"
          },
          'scaleLabel' : {
            'display'     : false,
            'labelString' : 'Date',
          }
        }
      },
      yAxes:{

          'scaleLabel' : {
            'display'     : false,
            'labelString' : 'Numbers',
          }
        }
    },
    // scales: {
    //   x: {
    //     display: true,
    //     scaleLabel: {
    //       display: true,
    //       labelString: "Month",
    //     },
    //   },
    //   y: {
    //     display: true,
    //     scaleLabel: {
    //       display: true,
    //       labelString: "Value",
    //     },
    //     min: 0,
    //     max: 100,
    //     ticks: {
    //       // forces step size to be 5 units
    //       stepSize: 5,
    //     },
    //   },
    // },
  }
};

const doughnutChart = {
  type: 'doughnut',
  data: {
    labels: ["Red", "Blue", "Yellow", "Green", "Purple"],
    datasets: [
      {
        label: "# of Votes",
        data: [12, 19, 3, 5, 2],
        backgroundColor: [
          "rgba(232, 116, 59 , 0.5 )",
          "rgba(88, 153, 218 , 0.5 )",
          "rgba(25, 169, 121 , 0.5 )",
          "rgba(237, 74, 123 , 0.5 )",
          "rgba(19, 164, 180 , 0.5 )",
        ],
        borderColor: [
          "rgba(232, 116, 59 , 1)",
          "rgba(88, 153, 218 , 1)",
          "rgba(25, 169, 121 , 1)",
          "rgba(237, 74, 123 , 1)",
          "rgba(19, 164, 180 , 1)",
        ],
        borderWidth: 1,
      },
    ],
  },
  options: {
    grid: {
      clickable: true,
      hoverable: true,
    },
    tooltips: {
      // mode: 'index',
      // intersect: false,
      backgroundColor: "#FFF",
      bodyFontColor: "#000",
      borderColor: "#727272",
      borderWidth: 2,
      titleFontColor: "#000",
    },
    series: {
      pie: {
        show: true,
        label: {
          show: true,
          radius: 7 / 8,
          formatter: function (label, series) {
            return (
              "<div style='font-size:8pt; text-align:center; padding:2px; color:white;'>" +
              label +
              " (" +
              Math.round(series.percent) +
              "%)</div>"
            );
          },
          background: {
            // opacity: 0.5,
            // color: '#000'
          },
        },
      },
    },
  },
};

const Chart = ({id, title, data}) => {
  // console.log(type)
  const useStyles = makeStyles((theme) => ({
    root: {
      // display: 'inline-block',
      // width: "100%",
      // width: "calc(100% - 350px)",
      paddingTop: '25px',
      height: data.chart.type === "doughnut" ? "200px" : "400px",
      // flexGrow: 3
    },
  }));
  const classes = useStyles();
  const chartContainer = useRef(null);
  const [chartInstance, setChartInstance] = useState(null);


  let chartConfig = lineChartConfig;
  chartConfig.type = data.chart.type;

  if (chartConfig.type === "line") {
    chartConfig = lineChartConfig;
  } else if (chartConfig.type === "doughnut") {
    chartConfig = doughnutChart;
  }
  chartConfig.data =  data.chart.data
  // chartConfig.options.title.text =  title

  useEffect(() => {
    if (chartContainer && chartContainer.current) {
      const newChartInstance = new Chartjs(chartContainer.current, chartConfig);
      setChartInstance(newChartInstance);
    }
  }, [chartContainer]);
  console.log(chartContainer.current)
  return (
    <div className={classes.root}>
      <canvas className={"Chart__canvas"+id} ref={chartContainer} />
    </div>
  );
};

export default Chart;
