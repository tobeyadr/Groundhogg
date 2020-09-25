import React from 'react';
import {
	ItemsCommaAndList,
	TagPicker,
	TagSpan,
} from '../../components/BasicControls/basicControls';
import { registerStepType, SimpleEditModal } from '../steps';

const { __, _x, _n, _nx } = wp.i18n;

registerStepType('remove_tag', {

	icon: ghEditor.steps.remove_tag.icon,
	group: ghEditor.steps.remove_tag.group,

	title: ({ data, context, settings }) => {

		if (!context || !context.tags_display ||
			!context.tags_display.length) {
			return <>{ __('Select tags to add...', 'groundhogg') }</>;
		}

		return <>{ _x('Remove', 'tag step title', 'groundhogg') } <ItemsCommaAndList
				separator={ '' }
				items={ context.tags_display.map(tag => <TagSpan
					tagName={ tag.label }
				/>) }/></>;
	},

	edit: ({ data, context, settings, updateSettings, commit, done }) => {

		const tagsChanged = (values) => {
			updateSettings({
				tags: values.map(tag => tag.value),
			}, {
				tags_display: values,
			});
		};

		return (
			<SimpleEditModal
				title={ __('Remove tags...', 'groundhogg') }
				done={ done }
				commit={ commit }
			>
				<TagPicker
					id={ 'tags' }
					value={ ( context && context.tags_display ) || false }
					update={ tagsChanged }
				/>
				<p className={ 'description' }>{ __(
					'Create a new tag by entering the name and pressing [enter]',
					'groundhogg') }</p>
			</SimpleEditModal>
		);
	},

});