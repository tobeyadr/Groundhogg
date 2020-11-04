/**
 * External dependencies
 */
import { Fragment, useState } from "@wordpress/element";
import { useSelect, useDispatch } from "@wordpress/data";
import { makeStyles } from "@material-ui/core/styles";
import { __ } from "@wordpress/i18n";
import Card from "@material-ui/core/Card";
import Typography from "@material-ui/core/Typography";
import ArrowDropUpIcon from '@material-ui/icons/ArrowDropUp';
import ArrowDropDownIcon from '@material-ui/icons/ArrowDropDown';

/**
 * Internal dependencies
 */


const Stats = ({ title, data, gridColumnStart, gridColumnEnd, gridRowStart, gridRowEnd }) => {
  console.log('stats', gridColumnStart, gridColumnEnd, gridRowStart, gridRowEnd)
  const useStyles = makeStyles((theme) => ({
    root: {
      display: 'inline-block',
      position: 'relative',
      // margin: "5px",
      gridColumnStart,
      gridColumnEnd,
      gridRowStart,
      gridRowEnd,
    },
    title: {
      display: 'block',
      fontSize: "18px",
      textTransform: "capitalize",
      padding: "10px 5px 5px 10px",
      color: '#ffffff',
      background: theme.palette.primary.main,
      marginBottom: '10px'
    },
    current: {
      fontSize: "50px",
      fontWeight: 900
    },
    compareArrow: {
      position: 'absolute',
      left: '-7px',
      bottom: '-15px',
      fontSize: '50px'
    },
    compare: {
      position: 'absolute',
      left: '85px',
      bottom: '0',
      fontSize: "12px",
      // fontWeight: 900
    },
    percent: {
      position: 'absolute',
      // left: '-45px',
      bottom: '-2px',
      fontSize: "18px",
      fontWeight: 700
    },
  }));

  const classes = useStyles();

  const { type} = data;
  const { current, compare } = data.chart.data;
  const { number } = data.chart.number;

  const { text,  percent } = data.chart.compare;
  const { direction, color } = data.chart.compare.arrow;

  const percentPosition = (-15*(percent.length-1)-5)+'px';
  const arrow = direction === "up" ? <ArrowDropUpIcon style={{color}} className={classes.compareArrow}/> : <ArrowDropDownIcon style={{color}} className={classes.compareArrow}/>;

  return (
    <Card className={classes.root}>
      <div
        className={classes.title}
      >
        {title}
      </div>
      <div
        className={classes.current}
      >
        {current}
      </div>

      <div
        className={classes.currentMetric}
      >
        {number}
      </div>

        {arrow}
      <div className={classes.compare}>
        <div style={{left:percentPosition}} className={classes.percent}>{percent}</div>{text}
      </div>
    </Card>
  );
};

export default Stats;