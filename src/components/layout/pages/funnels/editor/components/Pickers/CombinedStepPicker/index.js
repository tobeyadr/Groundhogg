import Tabs from '@material-ui/core/Tabs'
import Tab from '@material-ui/core/Tab'
import AppBar from '@material-ui/core/AppBar'
import { useState } from '@wordpress/element'
import BenchmarkPicker from '../BenchmarkPicker'
import ActionPicker from '../ActionPicker'
import ConditionPicker from '../ConditionPicker'
import {
  ACTIONS,
  BENCHMARKS,
  CONDITIONS,
} from 'components/layout/pages/funnels/editor/steps-types/constants'

const COMBINED_PICKER_WIDTH = 500;

const CombinedStepPicker = (props) => {

  const { showGroups } = props
  const [value, setValue] = useState( showGroups[0] )

  const handleChange = (event, newValue) => {
    setValue(newValue)
  }

  let Picker

  switch (value) {
    case BENCHMARKS:
      Picker = BenchmarkPicker
      break
    case ACTIONS:
      Picker = ActionPicker
      break
    case CONDITIONS:
      Picker = ConditionPicker
      break
  }

  return (
    <>
      <AppBar style={{width:COMBINED_PICKER_WIDTH}} position={ 'static' }>
        <Tabs value={ value } onChange={ handleChange }>
          { showGroups.includes( BENCHMARKS ) && <Tab
            value={ BENCHMARKS }
            label={ 'Benchmarks' }
          /> }
          { showGroups.includes( ACTIONS ) && <Tab
            value={ ACTIONS }
            label={ 'Actions' }
          /> }
          { showGroups.includes( CONDITIONS ) && <Tab
            value={ CONDITIONS }
            label={ 'Conditions' }
          /> }
        </Tabs>
      </AppBar>
      <div style={{width:COMBINED_PICKER_WIDTH}}>
        <Picker { ...props }/>
      </div>
    </>
  )
}

CombinedStepPicker.defaultProps = {
  showGroups: [
    BENCHMARKS,
    ACTIONS,
    CONDITIONS
  ]
}

export default CombinedStepPicker
