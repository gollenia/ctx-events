/**
 * Wordpress dependencies
 */
import { useBlockProps } from '@wordpress/block-editor';
import {
	Flex,
	FlexItem,
	__experimentalItem as Item,
	__experimentalItemGroup as ItemGroup,
	SelectControl,
	TextControl,
} from '@wordpress/components';
import { useEntityProp } from '@wordpress/core-data';
import { __ } from '@wordpress/i18n';

/**
 * Internal dependencies
 */

const edit = ({ context }) => {
	if (context.postType !== 'ctx-event-person') return null;

	const [meta, setMeta] = useEntityProp('postType', context.postType, 'meta');

	const blockProps = useBlockProps({
		className: ['person-edit'].filter(Boolean).join(' '),
	});

	return (
		<div {...blockProps}>
			<div className="person-edit__admin">
				<Flex>
					<FlexItem isBlock>
						<TextControl
							label={__('Organization', 'events')}
							value={meta._person_organization}
							onChange={(value) => {
								setMeta({
									...meta,
									_person_organization: value,
								});
							}}
						/>
					</FlexItem>
					<FlexItem isBlock>
						<TextControl
							label={__('Prefix', 'events')}
							value={meta._person_prefix}
							onChange={(value) => {
								setMeta({
									...meta,
									_person_prefix: value,
								});
							}}
						/>
					</FlexItem>
					<FlexItem isBlock>
						<TextControl
							label={__('Suffix', 'events')}
							value={meta._person_suffix}
							onChange={(value) => {
								setMeta({
									...meta,
									_person_suffix: value,
								});
							}}
						/>
					</FlexItem>
				</Flex>
				<Flex>
					<FlexItem isBlock>
						<TextControl
							label={__('Position', 'events')}
							value={meta._person_position}
							onChange={(value) => {
								setMeta({
									...meta,
									_person_position: value,
								});
							}}
						/>
					</FlexItem>
				</Flex>
				<Flex>
					<FlexItem isBlock>
						<SelectControl
							label={__('Gender', 'events')}
							value={meta._person_gender}
							options={[
								{ label: __('Select gender', 'events'), value: '' },
								{ label: __('Male', 'events'), value: 'male' },
								{ label: __('Female', 'events'), value: 'female' },
							]}
							onChange={(value) => {
								setMeta({
									...meta,
									_person_gender: value,
								});
							}}
						/>
					</FlexItem>
					<FlexItem isBlock>
						<TextControl
							label={__('First Name', 'events')}
							value={meta._person_first_name}
							onChange={(value) => {
								setMeta({
									...meta,
									_person_first_name: value,
								});
							}}
						/>
					</FlexItem>
					<FlexItem isBlock>
						<TextControl
							label={__('Last Name', 'events')}
							value={meta._person_last_name}
							onChange={(value) => {
								setMeta({
									...meta,
									_person_last_name: value,
								});
							}}
						/>
					</FlexItem>
				</Flex>
				<Flex>
					<FlexItem isBlock>
						<TextControl
							label={__('E-Mail', 'events')}
							value={meta._person_email}
							onChange={(value) => {
								setMeta({
									...meta,
									_person_email: value,
								});
							}}
						/>
					</FlexItem>
					<FlexItem isBlock>
						<TextControl
							label={__('Telephone', 'events')}
							value={meta._person_phone}
							onChange={(value) => {
								setMeta({
									...meta,
									_person_phone: value,
								});
							}}
						/>
					</FlexItem>
				</Flex>
				<ItemGroup>
					{meta._person_same_as.map((sameAs, index) => (
						<Item key={index}>
							<TextControl
								label={__('Social Link', 'events')}
								value={sameAs}
								onChange={(value) => {
									const newSameAs = [...meta._person_same_as];
									newSameAs[index] = value;
									setMeta({
										...meta,
										_person_same_as: newSameAs,
									});
								}}
							/>
						</Item>
					))}
				</ItemGroup>
				<button
					type="button"
					className="button button-secondary"
					onClick={() => {
						const newSameAs = [...meta._person_same_as, ''];
						setMeta({
							...meta,
							_person_same_as: newSameAs,
						});
					}}
				>
					{__('Add Social Link', 'events')}
				</button>
			</div>
		</div>
	);
};

export default edit;
