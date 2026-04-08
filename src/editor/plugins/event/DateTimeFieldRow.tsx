import { Flex, FlexItem, TextControl } from '@wordpress/components';

type Props = {
	label: string;
	date: string;
	time: string;
	showTime: boolean;
	disabled?: boolean;
	onDateChange: (value: string) => void;
	onTimeChange: (value: string) => void;
	minDate?: string;
	minTime?: string;
};

const labelStyle = {
	marginBottom: '8px',
	fontSize: '11px',
	textTransform: 'uppercase' as const,
};

const DateTimeFieldRow = ({
	label,
	date,
	time,
	showTime,
	disabled = false,
	onDateChange,
	onTimeChange,
	minDate,
	minTime,
}: Props) => {
	return (
		<div>
			<p style={labelStyle}>
				<strong>{label}</strong>
			</p>
			<Flex>
				<FlexItem isBlock>
					<TextControl
						type="date"
						__next40pxDefaultSize
						__nextHasNoMarginBottom
						value={date}
						min={minDate}
						disabled={disabled}
						onChange={onDateChange}
					/>
				</FlexItem>
				{showTime ? (
					<FlexItem>
						<TextControl
							type="time"
							__next40pxDefaultSize
							__nextHasNoMarginBottom
							value={time}
							min={minTime}
							disabled={disabled}
							onChange={onTimeChange}
						/>
					</FlexItem>
				) : null}
			</Flex>
		</div>
	);
};

export default DateTimeFieldRow;
