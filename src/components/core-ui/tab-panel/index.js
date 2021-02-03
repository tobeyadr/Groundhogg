/**
 * External dependencies
 */
import { useEffect, useState } from '@wordpress/element';
import PropTypes from 'prop-types';
import {Box, Card, makeStyles, Paper, Tab, Tabs, Typography } from '@material-ui/core';
import {useHistory, useLocation, Link } from 'react-router-dom';

function TabPanel(props) {
  const { children, value, index, ...other } = props;

  return (
    <div
      role="tabpanel"
      hidden={value !== index}
      id={`scrollable-auto-tabpanel-${index}`}
      aria-labelledby={`scrollable-auto-tab-${index}`}
      {...other}
    >
      {value === index && (
        <Box p={3}>
          <Typography>{children}</Typography>
        </Box>
      )}
    </div>
  );
}

TabPanel.propTypes = {
  children: PropTypes.node,
  index: PropTypes.any.isRequired,
  value: PropTypes.any.isRequired,
};

function a11yProps(index) {
  return {
    id: `scrollable-auto-tab-${index}`,
    'aria-controls': `scrollable-auto-tabpanel-${index}`,
  };
}

const useStyles = makeStyles((theme) => ({
  root: {
    flexGrow: 1,
    width: '100%',
    minHeight: '2000px',
    backgroundColor: theme.palette.background.paper,
    marginTop: '20px',
  },
  kpiTitle: {
    fontSize: '24px',
    fontStyle: 'bold'
  },
  kpiMetric: {
    fontSize: '16px'
  },
  tabBar: {
    borderBottom: '1px solid #e0e0e0'
  }
}));

export default function ScrollableTabsButtonAuto( { tabs, handleChangeHook } ) {
  let defaultTab = 0;
  const location = useLocation();

  tabs.forEach((tab,i)=>{
    if(tab.route === location.pathname.replace('/','')){
      defaultTab = i
    }
  });

  const classes = useStyles();
  const [value, setValue] = useState(defaultTab);
  const history = useHistory();

  const handleChange = (event, newValue) => {
    console.log(event, tabs[newValue].route)
    setValue(newValue);
    history.push(tabs[newValue].route)
    handleChangeHook(tabs[newValue].route);
  };




  return (
    <Paper className={classes.root}>
        <Tabs
          className={classes.tabBar}
          value={value}
          onChange={handleChange}
          indicatorColor="primary"
          textColor="primary"
          variant="scrollable"
          scrollButtons="auto"
          aria-label="scrollable auto tabs"
        >
          {
            tabs.map( ( tab, index ) => (
               <Tab key={index} label={tab.label} {...a11yProps( { index } )} />
              )
            )
          }
        </Tabs>
        {
          tabs.map( ( tab, index ) => (
            <TabPanel value={value} index={index} key={index}>
              <tab.component classes={classes} />
            </TabPanel>
           )
          )
        }
    </Paper>
  );
}
