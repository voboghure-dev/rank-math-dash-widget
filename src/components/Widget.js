import { __ } from '@wordpress/i18n';
import { useState, useEffect } from '@wordpress/element';
import { SelectControl } from '@wordpress/components';
import apiFetch from '@wordpress/api-fetch';
import { LineChart, Line, XAxis, YAxis, CartesianGrid, Tooltip, Legend, ResponsiveContainer } from 'recharts';

function Widget() {
	const [data, setData] = useState();

	const fetchData = async (days) => {
		try {
			const res = await apiFetch({ path: `/rmdw/v1/recharts-data/${days}` });
			setData(res);
		} catch (error) {
			console.log(error);
		}
	};

	const filterData = (value) => {
		fetchData(value);
	};

	useEffect(() => {
		fetchData(7);
	}, []);

	return (
		<>
			<div style={{ display: 'flex', justifyContent: 'space-between', 'padding-bottom': '15px' }}>
				<div>
					<h2 style={{ 'padding-top': 0 }}>{__('Graph Widget', 'myrank-math-dash-widgetguten')}</h2>
				</div>
				<div>
					<SelectControl
						options={[
							{ label: __('7 Days', 'myrank-math-dash-widgetguten'), value: '7' },
							{ label: __('15 Days', 'myrank-math-dash-widgetguten'), value: '15' },
							{ label: __('1 month', 'myrank-math-dash-widgetguten'), value: '30' },
						]}
						onChange={filterData}
					/>
				</div>
			</div>

			<div style={{ width: '100%', height: 300 }}>
				<ResponsiveContainer>
					<LineChart
						width={500}
						height={300}
						data={data}
						margin={{
							top: 5,
							right: 30,
							left: 20,
							bottom: 5,
						}}
					>
						<CartesianGrid strokeDasharray='3 3' />
						<XAxis dataKey='name' />
						<YAxis />
						<Tooltip />
						<Legend />
						<Line type='monotone' dataKey='pv' stroke='#8884d8' activeDot={{ r: 8 }} />
						<Line type='monotone' dataKey='uv' stroke='#82ca9d' />
					</LineChart>
				</ResponsiveContainer>
			</div>
		</>
	);
}

export default Widget;
