const Dummy = () => {
	return (
		<div className="wp-table-wrapper">
			<table className={`wp-list-table widefat fixed striped dummy`}>
				<thead>
					<tr>
						<td
							id="cb"
							style={{ width: '1%' }}
							className="manage-field field-cb check-field"
						>
							<input id="cb-select-all-1" type="checkbox" />
						</td>

						{[1, 2, 3].map((field, index) => {
							return (
								<th key={index} scope="col">
									Field {field}
								</th>
							);
						})}
					</tr>
				</thead>
				<tbody>
					<tr>
						<td colSpan={4}></td>
					</tr>
					<tr>
						<td colSpan={4}></td>
					</tr>
					<tr>
						<td colSpan={4}></td>
					</tr>
				</tbody>
			</table>
		</div>
	);
};

export default Dummy;
