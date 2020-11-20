/**
 * WordPress dependencies
 */
import '@wordpress/format-library';
import { __ } from '@wordpress/i18n';
import { useSelect, useDispatch } from '@wordpress/data';
import { useEffect, useState, useMemo } from '@wordpress/element';
import { serialize, parse } from '@wordpress/blocks';
import { uploadMedia } from '@wordpress/media-utils';
import {
	BlockEditorKeyboardShortcuts,
	BlockEditorProvider,
	BlockList,
	BlockInspector,
	WritingFlow,
	ObserveTyping,
	Typewriter,
	CopyHandler,
	BlockSelectionClearer,
	MultiSelectScrollIntoView,
} from '@wordpress/block-editor';

import {
	VisualEditorGlobalKeyboardShortcuts,
} from '@wordpress/editor';

import { Popover } from '@wordpress/components';

/**
 * External dependencies
 */
import Paper from '@material-ui/core/Paper';
import Grid from '@material-ui/core/Grid';
import TextField from '@material-ui/core/TextField'
import { makeStyles } from "@material-ui/core/styles";

/**
 * Internal dependencies
 */
import Sidebar from '../sidebar';

//TODO Implement block persistence with email data store.
//TODO Potentially use our own alerts data store (core).

const useStyles = makeStyles((theme) => ({
    subjectInputs: {
		width: "calc(100% - 10px)",
		padding: '.5em 0'
    },
  }));

function BlockEditor( { settings: _settings, subject, handleSubjectChange, preHeader, handlePreHeaderChange, content, handleContentChange } ) {
	const [ blocks, updateBlocks ] = useState( [] );
	const { createInfoNotice } = useDispatch( 'core/notices' );
	const classes = useStyles();
	const canUserCreateMedia = useSelect( ( select ) => {
		const _canUserCreateMedia = select( 'core' ).canUser( 'create', 'media' );
		return _canUserCreateMedia || _canUserCreateMedia !== false;
	}, [] );

	const settings = useMemo(() => {
		if ( ! canUserCreateMedia ) {
			return _settings;
		}
		return {
			..._settings,
			mediaUpload( { onError, ...rest } ) {
				uploadMedia( {
					wpAllowedMimeTypes: _settings.allowedMimeTypes,
					onError: ( { message } ) => onError( message ),
					...rest,
				} );
			},
		};
	}, [ canUserCreateMedia, _settings ] );

	useEffect( () => {
		// const storedBlocks = window.localStorage.getItem( 'groundhoggBlocks' );

		if (content?.length ) {
			handleUpdateBlocks(() => parse(content));
		}
	}, [] );

	const handleUpdateBlocks = (blocks) => {
		updateBlocks( blocks );
	}

	const handlePersistBlocks = ( newBlocks ) => {
		// updateBlocks( newBlocks );
		// console.log('handlePersistBlocks' , newBlocks)
		// window.localStorage.setItem( 'groundhoggBlocks', serialize( newBlocks ) )
		handleContentChange(blocks)
	}

	if ( ! settings.hasOwnProperty( '__experimentalBlockPatterns' ) ) {
		settings.__experimentalBlockPatterns = [];
	}


	return (
		<div className="groundhogg-block-editor">
			<BlockEditorProvider
				value={ blocks }
				settings={ settings }
				onInput={ handleUpdateBlocks }
				onChange={ handlePersistBlocks }
			>
				<Grid container spacing={3}>
					<Grid item xs={9}>
						<form noValidate autoComplete="off">
							<TextField className={ classes.subjectInputs } onChange={handleSubjectChange} label={'Subject'} value={subject} />
							<TextField className={ classes.subjectInputs } onChange={handlePreHeaderChange} label={'Pre Header'} value={preHeader} placeholder={ __( 'Pre Header Text: Used to summarize the content of the email.' ) } />
						</form>
						<Paper>
							<BlockSelectionClearer
					className="edit-post-visual-editor editor-styles-wrapper"
							>
								<VisualEditorGlobalKeyboardShortcuts />
								<MultiSelectScrollIntoView />
								<Popover.Slot name="block-toolbar" />
								<BlockEditorKeyboardShortcuts.Register />
								<Typewriter>
									<CopyHandler>
										<WritingFlow>
											<ObserveTyping>
												<BlockList />
											</ObserveTyping>
										</WritingFlow>
									</CopyHandler>
								</Typewriter>
							</BlockSelectionClearer>
						</Paper>
					</Grid>
					<Sidebar.InspectorFill>
						<BlockInspector />
					</Sidebar.InspectorFill>
				</Grid>
			</BlockEditorProvider>
		</div>
	);
}

export default BlockEditor;
