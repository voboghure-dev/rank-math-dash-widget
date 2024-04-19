import React, { useState, useEffect } from 'react';
import axios from 'axios';
import { LineChart, Line, XAxis, YAxis, CartesianGrid, Tooltip, Legend, ResponsiveContainer } from 'recharts';

function Widget() {
	const [data, setData] = useState();

	const fetchData = async (days) => {
		try {
			const res = await axios.get(`/wp-json/rmdw/v1/recharts-data/${days}`);
			setData(res.data);
		} catch (error) {
			console.log(first);
		}
	};

	const filterData = (e) => {
		fetchData(e.target.value);
	};

	useEffect(() => {
		fetchData(7);
	}, []);

	return (
		<>
			<div style={{ display: 'flex', justifyContent: 'space-between', 'padding-bottom': '15px' }}>
				<div>
					<h2 style={{ 'padding-top': 0 }}>Graph Widget</h2>
				</div>
				<div>
					<select onChange={filterData}>
						<option value='7'>7 Days</option>
						<option value='15'>15 Days</option>
						<option value='30'>1 month</option>
					</select>
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
