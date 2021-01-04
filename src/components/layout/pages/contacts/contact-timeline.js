import React from 'react';
import {makeStyles} from '@material-ui/core/styles';
import Timeline from '@material-ui/lab/Timeline';
import TimelineItem from '@material-ui/lab/TimelineItem';
import TimelineSeparator from '@material-ui/lab/TimelineSeparator';
import TimelineConnector from '@material-ui/lab/TimelineConnector';
import TimelineContent from '@material-ui/lab/TimelineContent';
import TimelineOppositeContent from '@material-ui/lab/TimelineOppositeContent';
import TimelineDot from '@material-ui/lab/TimelineDot';
import FastfoodIcon from '@material-ui/icons/Fastfood';
import LaptopMacIcon from '@material-ui/icons/LaptopMac';
import HotelIcon from '@material-ui/icons/Hotel';
import RepeatIcon from '@material-ui/icons/Repeat';
import Paper from '@material-ui/core/Paper';
import Typography from '@material-ui/core/Typography';
import {applyFilters} from "@wordpress/hooks";
import TextField from "@material-ui/core/TextField";
import Checkbox from "@material-ui/core/Checkbox";
import EventIcon from '@material-ui/icons/Event';
import DraftsIcon from '@material-ui/icons/Drafts';

const useStyles = makeStyles((theme) => ({

    secondaryTail: {
        backgroundColor: theme.palette.primary.main
    },
    timeline: {
        transform: "rotate(90deg)"
    },
    timelineContentContainer: {
        textAlign: "left"
    },
    timelineContent: {
        // display: "inline-block",
        // transform: "rotate(-90deg)",
        // textAlign: "center",
        // minWidth: 50,
        // padding: '6px 16px',
        // flex :0

    },
    timelineIcon: {
        transform: "rotate(-90deg)"
    },
    opposite: {
        flex: 0,
        padding: 0,
        marginRight: 0,

        // text-align: right ,
        // margin-right: 0;
    },
    content: {
        flex: 0
    }

}));

export const ContactTimeline = () => {
    const classes = useStyles();

    return (
        <div>
            <Timeline>
                <TimelineItem>
                    <TimelineOppositeContent className={classes.opposite}/>
                    <TimelineSeparator>
                        <TimelineDot color="primary">
                            <TimelineIcon type={'email_open'}/>
                        </TimelineDot>
                        <TimelineConnector className={classes.secondaryTail}/>
                    </TimelineSeparator>
                    <TimelineContent>
                        Eat

                        <Paper elevation={3} className={classes.paper}>
                            <Typography variant="h6" component="h1">
                                Eat
                            </Typography>
                            <Typography>Because you need strength</Typography>
                        </Paper>
                    </TimelineContent>
                </TimelineItem>
                <TimelineItem>
                    <TimelineOppositeContent className={classes.opposite}/>
                    <TimelineSeparator>
                        <TimelineDot/>
                        <TimelineConnector/>
                    </TimelineSeparator>
                    <TimelineContent className={classes.content}>
                        <Typography>Code</Typography>
                    </TimelineContent>
                </TimelineItem>
                <TimelineItem>
                    <TimelineOppositeContent className={classes.opposite}/>
                    <TimelineSeparator>
                        <TimelineDot/>
                        <TimelineConnector/>
                    </TimelineSeparator>
                    <TimelineContent className={classes.content}>
                        <Typography>Sleep</Typography>
                    </TimelineContent>
                </TimelineItem>
                <TimelineItem>
                    <TimelineOppositeContent className={classes.opposite}/>
                    <TimelineSeparator>
                        <TimelineDot/>
                        {/*<TimelineConnector />*/}
                    </TimelineSeparator>
                    <TimelineContent className={classes.content}>
                        <Typography>Repeat</Typography>
                    </TimelineContent>
                </TimelineItem>
            </Timeline>
        </div>
    );
}
export const TimelineIcon = ({type}) => {

    const mapping = applyFilters('groundhogg.contacts.timeline.icons', {
        'email_opened': {component: DraftsIcon},

    })
    if (mapping.hasOwnProperty(type)) {
        const mappedComponent = mapping[type];

        return <mappedComponent.component/>

    } else {
        return <EventIcon/>

    }


}