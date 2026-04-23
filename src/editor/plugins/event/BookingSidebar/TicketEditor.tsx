import {
	Button,
	Flex,
	FlexItem,
	TextareaControl,
	TextControl,
} from '@wordpress/components';
import { useEffect, useState } from '@wordpress/element';
import { __ } from '@wordpress/i18n';

import { formatCentsAsPriceInput, parsePriceInputToCents } from './price';
import type { BookingTicket } from './types';

type TicketEditorProps = {
	ticket: BookingTicket;
	onUpdate: () => void;
	onCancel: () => void;
	setTicket: (ticket: BookingTicket) => void;
};

const TicketEditor = ({
	ticket,
	onUpdate,
	onCancel,
	setTicket,
}: TicketEditorProps) => {
	const [priceInput, setPriceInput] = useState(() =>
		formatCentsAsPriceInput(ticket.ticket_price),
	);

	useEffect(() => {
		setPriceInput(formatCentsAsPriceInput(ticket.ticket_price));
	}, [ticket.ticket_id]);

	return (
		<>
			<Flex direction="column">
				<FlexItem>
					<TextControl
						label={__('Name', 'ctx-events')}
						type="text"
						value={ticket.ticket_name}
						onChange={(value) => setTicket({ ...ticket, ticket_name: value })}
					/>
				</FlexItem>
				<FlexItem>
					<TextareaControl
						label={__('Description', 'ctx-events')}
						value={ticket.ticket_description}
						onChange={(value) =>
							setTicket({
								...ticket,
								ticket_description: value,
							})
						}
					/>
				</FlexItem>
				<Flex justify="flex-start">
					<FlexItem style={{ flex: 1 }}>
						<TextControl
							label={__('Price', 'ctx-events')}
							type="text"
							value={priceInput}
							onChange={(value) => {
								setPriceInput(value);
								setTicket({
									...ticket,
									ticket_price: parsePriceInputToCents(value),
								});
							}}
							onBlur={() => {
								setPriceInput(formatCentsAsPriceInput(ticket.ticket_price));
							}}
						/>
					</FlexItem>
					<FlexItem style={{ flex: 1 }}>
						<TextControl
							label={__('Spaces', 'ctx-events')}
							type="number"
							min={0}
							value={String(ticket.ticket_spaces)}
							onChange={(value) =>
								setTicket({
									...ticket,
									ticket_spaces: value,
								})
							}
						/>
					</FlexItem>
				</Flex>
				<Flex>
					<FlexItem style={{ flex: 1 }}>
						<TextControl
							label={__('Minimum bookable', 'ctx-events')}
							type="number"
							min={0}
							value={String(ticket.ticket_min)}
							onChange={(value) => setTicket({ ...ticket, ticket_min: value })}
						/>
					</FlexItem>
					<FlexItem style={{ flex: 1 }}>
						<TextControl
							label={__('Maximum bookable', 'ctx-events')}
							type="number"
							min={1}
							value={String(ticket.ticket_max)}
							onChange={(value) => setTicket({ ...ticket, ticket_max: value })}
						/>
					</FlexItem>
				</Flex>
				<FlexItem>
					<TextControl
						label={__('Start date', 'ctx-events')}
						type="date"
						value={ticket.ticket_start}
						onChange={(value) => setTicket({ ...ticket, ticket_start: value })}
					/>
				</FlexItem>
				<FlexItem>
					<TextControl
						label={__('Order', 'ctx-events')}
						type="number"
						value={String(ticket.ticket_order)}
						onChange={(value) =>
							setTicket({
								...ticket,
								ticket_order: Number.parseInt(value, 10) || 0,
							})
						}
					/>
				</FlexItem>
			</Flex>
			<Flex justify="flex-end" style={{ marginTop: '1rem' }}>
				<FlexItem>
					<Button variant="secondary" onClick={onCancel}>
						{__('Cancel', 'ctx-events')}
					</Button>
				</FlexItem>
				<FlexItem>
					<Button
						variant="primary"
						disabled={!ticket.ticket_name}
						onClick={onUpdate}
					>
						{__('Save', 'ctx-events')}
					</Button>
				</FlexItem>
			</Flex>
		</>
	);
};

export default TicketEditor;
