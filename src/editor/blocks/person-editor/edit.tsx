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

type PersonMeta = {
	_person_organization?: string;
	_person_prefix?: string;
	_person_suffix?: string;
	_person_position?: string;
	_person_gender?: string;
	_person_first_name?: string;
	_person_last_name?: string;
	_person_email?: string;
	_person_phone?: string;
	_person_same_as?: string[];
};

type EditProps = {
	context: {
		postType?: string;
	};
};

const edit = ({ context }: EditProps) => {
	if (context.postType !== 'ctx-event-person') {
		return null;
	}

	const [meta, setMeta] = useEntityProp('postType', context.postType, 'meta') as [
		PersonMeta,
		(value: PersonMeta) => void,
	];

	const socialLinks = Array.isArray(meta._person_same_as)
		? meta._person_same_as
		: [];

	const blockProps = useBlockProps({
		className: 'person-edit',
	});

	return (
		<div {...blockProps}>
			<div className="person-edit__admin">
				<Flex>
					<FlexItem isBlock>
						<TextControl
							label={__('Organization', 'ctx-events')}
							value={meta._person_organization ?? ''}
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
							label={__('Prefix', 'ctx-events')}
							value={meta._person_prefix ?? ''}
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
							label={__('Suffix', 'ctx-events')}
							value={meta._person_suffix ?? ''}
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
							label={__('Position', 'ctx-events')}
							value={meta._person_position ?? ''}
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
							label={__('Gender', 'ctx-events')}
							value={meta._person_gender ?? ''}
							options={[
								{ label: __('Select gender', 'ctx-events'), value: '' },
								{ label: __('Male', 'ctx-events'), value: 'male' },
								{ label: __('Female', 'ctx-events'), value: 'female' },
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
							label={__('First Name', 'ctx-events')}
							value={meta._person_first_name ?? ''}
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
							label={__('Last Name', 'ctx-events')}
							value={meta._person_last_name ?? ''}
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
							label={__('E-Mail', 'ctx-events')}
							value={meta._person_email ?? ''}
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
							label={__('Telephone', 'ctx-events')}
							value={meta._person_phone ?? ''}
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
					{socialLinks.map((sameAs, index) => (
						<Item key={index}>
							<TextControl
								label={__('Social Link', 'ctx-events')}
								value={sameAs}
								onChange={(value) => {
									const newSameAs = [...socialLinks];
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
						setMeta({
							...meta,
							_person_same_as: [...socialLinks, ''],
						});
					}}
				>
					{__('Add Social Link', 'ctx-events')}
				</button>
			</div>
		</div>
	);
};

export default edit;
