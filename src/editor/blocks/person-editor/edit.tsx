import { useBlockProps } from '@wordpress/block-editor';
import {
	Button,
	Flex,
	FlexItem,
	Icon,
	__experimentalItem as Item,
	__experimentalItemGroup as ItemGroup,
	SelectControl,
	TextControl,
} from '@wordpress/components';
import { useEntityProp } from '@wordpress/core-data';
import { __ } from '@wordpress/i18n';
import { trash } from '@wordpress/icons';
import { formatPersonName, type PersonNameMeta } from '@events/utilities';

type PersonMeta = PersonNameMeta & {
	_person_organization?: string;
	_person_position?: string;
	_person_gender?: string;
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

	const [meta, setMeta] = useEntityProp(
		'postType',
		context.postType,
		'meta',
	) as [PersonMeta, (value: PersonMeta) => void];
	const [title, setTitle] = useEntityProp(
		'postType',
		context.postType,
		'title',
	) as [string, (value: string) => void];

	const updatePersonMeta = (nextMeta: PersonMeta) => {
		setMeta(nextMeta);

		const nextTitle = formatPersonName(nextMeta);

		if (nextTitle !== title) {
			setTitle(nextTitle);
		}
	};

	const socialLinks = Array.isArray(meta._person_same_as)
		? meta._person_same_as
		: [];

	const blockProps = useBlockProps({
		className: 'person-edit inline-editor',
	});

	return (
		<div {...blockProps}>
			<Flex direction="column" gap={4}>
				<Flex gap={4}>
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
								updatePersonMeta({
									...meta,
									_person_gender: value,
								});
							}}
							__next40pxDefaultSize
						/>
					</FlexItem>
					<FlexItem isBlock>
						<TextControl
							label={__('Prefix', 'ctx-events')}
							value={meta._person_prefix ?? ''}
							onChange={(value) => {
								updatePersonMeta({
									...meta,
									_person_prefix: value,
								});
							}}
							__next40pxDefaultSize
						/>
					</FlexItem>
					<FlexItem isBlock>
						<TextControl
							label={__('Suffix', 'ctx-events')}
							value={meta._person_suffix ?? ''}
							onChange={(value) => {
								updatePersonMeta({
									...meta,
									_person_suffix: value,
								});
							}}
							__next40pxDefaultSize
						/>
					</FlexItem>
				</Flex>

				<Flex gap={4}>
					<FlexItem isBlock>
						<TextControl
							required
							label={__('First Name', 'ctx-events')}
							value={meta._person_first_name ?? ''}
							onChange={(value) => {
								updatePersonMeta({
									...meta,
									_person_first_name: value,
								});
							}}
							__next40pxDefaultSize
						/>
					</FlexItem>
					<FlexItem isBlock>
						<TextControl
							required
							label={__('Last Name', 'ctx-events')}
							value={meta._person_last_name ?? ''}
							onChange={(value) => {
								updatePersonMeta({
									...meta,
									_person_last_name: value,
								});
							}}
							__next40pxDefaultSize
						/>
					</FlexItem>
				</Flex>
				<Flex gap={4}>
					<FlexItem isBlock>
						<TextControl
							required
							label={__('E-Mail', 'ctx-events')}
							value={meta._person_email ?? ''}
							onChange={(value) => {
								updatePersonMeta({
									...meta,
									_person_email: value,
								});
							}}
							__next40pxDefaultSize
						/>
					</FlexItem>
					<FlexItem isBlock>
						<TextControl
							label={__('Telephone', 'ctx-events')}
							value={meta._person_phone ?? ''}
							onChange={(value) => {
								updatePersonMeta({
									...meta,
									_person_phone: value,
								});
							}}
							__next40pxDefaultSize
						/>
					</FlexItem>
				</Flex>
				<Flex gap={4}>
					<FlexItem isBlock>
						<TextControl
							label={__('Organization', 'ctx-events')}
							value={meta._person_organization ?? ''}
							onChange={(value) => {
								updatePersonMeta({
									...meta,
									_person_organization: value,
								});
							}}
							__next40pxDefaultSize
						/>
					</FlexItem>
					<FlexItem isBlock>
						<TextControl
							label={__('Position', 'ctx-events')}
							value={meta._person_position ?? ''}
							onChange={(value) => {
								updatePersonMeta({
									...meta,
									_person_position: value,
								});
							}}
							__next40pxDefaultSize
						/>
					</FlexItem>
				</Flex>
				<h4>{__('Social Media Links', 'ctx-events')}</h4>
				<ItemGroup>
					{socialLinks.map((sameAs, index) => (
						<Item key={index}>
							<Flex gap={4} align="flex-end">
								<FlexItem isBlock flex="1">
									<TextControl
										label={__('Social Media Link', 'ctx-events')}
										value={sameAs}
										onChange={(value) => {
											const newSameAs = [...socialLinks];
											newSameAs[index] = value;
											updatePersonMeta({
												...meta,
												_person_same_as: newSameAs,
											});
										}}
										__next40pxDefaultSize
									/>
								</FlexItem>
								<Button
									className="button button-secondary"
									onClick={() => {
										const newSameAs = [...socialLinks];
										newSameAs.splice(index, 1);
										updatePersonMeta({
											...meta,
											_person_same_as: newSameAs,
										});
									}}
									variant="secondary"
									__next40pxDefaultSize
								>
									<Icon icon={trash} />
								</Button>
							</Flex>
						</Item>
					))}
				</ItemGroup>
				<Button
					className="button button-secondary"
					onClick={() => {
						updatePersonMeta({
							...meta,
							_person_same_as: [...socialLinks, ''],
						});
					}}
					variant="secondary"
				>
					{__('Add Social Media Link', 'ctx-events')}
				</Button>
			</Flex>
		</div>
	);
};

export default edit;
