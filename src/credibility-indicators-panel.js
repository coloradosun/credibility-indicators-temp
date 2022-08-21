// Dependencies.
import { __ } from '@wordpress/i18n';
import {
	CheckboxControl,
	Flex,
	FlexItem,
	Tooltip,
} from '@wordpress/components';
import { useEntityProp } from '@wordpress/core-data';
import { select, useSelect } from '@wordpress/data';
import { PluginDocumentSettingPanel } from '@wordpress/edit-post';
import { useState } from '@wordpress/element';

/**
 * Panel UI for credibility indicators.
 */
const CredibilityIndicatorsPanel = () => {
	// Validate post type.
	const postType = useSelect( () => select( 'core/editor' ).getCurrentPostType(), [] );
	if ( ! [ 'post' ].includes( postType ) ) {
		return null;
	}

	// Get meta.
	const [ meta, setMeta ] = useEntityProp( 'postType', postType, 'meta' );
	const {
		credibility_indicators: currentIndicatorStatues,
	} = meta;

	/**
	 * Get all credibility indicators.
	 */
	const allCredibilityIndicators = useSelect(
		( select ) =>  select( 'core/editor' ).getEditedPostAttribute( 'credibility_indicators' ),
		[]
	);

	/**
	 * Map slugs into an object of default statuses.
	 */
	const defaultIndicatorStatuses = Object.fromEntries(
		new Map( allCredibilityIndicators.map( ( { slug } ) => [ slug, false ] ) )
	);

	/**
	 * Merge defaults with post meta.
	 */
	const selectedIndicators = {
		...defaultIndicatorStatuses,
		...meta.credibility_indicators,
	};

	return(
		<PluginDocumentSettingPanel
			title={ __( 'Credibility Indicators', 'credibility-indicators' ) }
		>
			{ allCredibilityIndicators.map( ( { description, label, slug, icon } ) => (
				<CheckboxControl
					key={ slug }
					label={ label }
					checked={ selectedIndicators[ slug ] }
					onChange={ () => {
						const newMeta = {
							...meta,
							credibility_indicators: {
								...selectedIndicators,
								[ slug ]: ! selectedIndicators[ slug ],
							},
						};

						setMeta( newMeta );
					} }
				/>
			) ) }
		</PluginDocumentSettingPanel>
	);
};

export default CredibilityIndicatorsPanel;