import { Fragment } from '@wordpress/element'
import { __ } from '@wordpress/i18n'
import { applyFilters } from '@wordpress/hooks'
import { filter, forEach } from 'lodash'
import TabPanel from 'components/core-ui/tab-panel'
import { SettingsPanel } from './settings-panel'

export const Settings = ({history, match}) => {
	const getTabs = () => {
		return applyFilters(
			'groundhogg.settings.tabs',
			window.Groundhogg.preloadSettings.tabs
		);
	}

	const prepareSections = ( id, sections ) => {
		let tabSections = filter( sections, ( section ) => ( section.tab === id ) );

		tabSections.map( ( section, index ) => (
			tabSections[ index ].settings = filter( applyFilters( 'groundhogg.settings.settings', window.Groundhogg.preloadSettings.settings ), ( setting ) => ( setting.section === section.id ) )
		) );

		return tabSections;
	}

	const tabs = [];

	forEach( getTabs(), ( tab ) => {
		let section = prepareSections( tab.id, applyFilters( 'groundhogg.settings.sections', window.Groundhogg.preloadSettings.sections ) );
		tabs.push({
			label: tab.title,
			route: tab.title.toLowerCase(),
			component : () => {
				return (
				  <SettingsPanel section={ section } />
				);
			}
		})
	} );

	return (
		<Fragment>
			<TabPanel tabs={tabs} enableRouting={true} history={history} match={match} />;
		</Fragment>
	);
};
