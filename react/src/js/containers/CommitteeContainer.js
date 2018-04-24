import React, { Component } from 'react';
import PropTypes from 'prop-types';

import Summary from './../components/charts/Summary';
import Ranking from './../components/charts/Ranking';
import Select from './../components/modules/Select';
import { BarChart, Bar, XAxis, YAxis, CartesianGrid, Tooltip, Legend, ResponsiveContainer } from 'recharts';

import data from './../fakeData/data';

class CommitteeContainer extends Component {
    // CALL {committeeSelected} to get the committee value selected

    render() {
        const { committees, committeeSelected } = this.props;

        return (
            <div className="committee__ctn">
                <h2 className="ctn__title">Comités</h2>
                <div className="committee__ctn__summary">
                    <Summary summaryTotal={876} summaryDescription={'Comités créés'} />
                    <Summary
                        summaryTotal={76}
                        summaryDescription={'Inscrits dans un comité'}
                        womanPercentage={`${33}%`}
                        manPercentage={`${67}%`}
                    />
                </div>

                <div className="committee__ctn__ranking">
                    <Ranking committees={committees} rankingTitle={'Comites les plus actifs'} />
                    <Ranking committees={committees} rankingTitle={'Comites les moins actifs'} />
                </div>

                <div className="committee__ctn__select">
                    <Select committees={committees} id={'selectCommittee'} name={'selectCommittee'} />
                </div>
                <div className="committee__ctn__bars">
                    <ResponsiveContainer>
                        <BarChart
                            width={600}
                            height={400}
                            data={data}
                            margin={{
                                top: 50,
                                right: 30,
                                left: 20,
                                bottom: 5,
                            }}>
                            <CartesianGrid stroke={'#FEF2F2'} vertical={false} />
                            <XAxis dataKey="name" stroke="" />
                            <YAxis stroke={'#FEF2F2'} />
                            <Tooltip
                                cursor={{
                                    stroke: '#FEF0F0',
                                    fill: '#FFF',
                                }}
                                itemStyle={{
                                    textAlign: 'left',
                                    stroke: '#FEF0F0',
                                }}
                            />
                            <Legend height={50} align="left" verticalAlign="bottom" iconType="circle" />
                            <Bar
                                name={committeeSelected}
                                dataKey={'adherent'}
                                fill={'#F8BCBC'}
                                barSize={10}
                                animationEasing={'ease-in-out'}
                            />
                        </BarChart>
                    </ResponsiveContainer>
                    <ResponsiveContainer>
                        <BarChart
                            width={600}
                            height={400}
                            data={data}
                            margin={{
                                top: 50,
                                right: 30,
                                left: 20,
                                bottom: 5,
                            }}>
                            <CartesianGrid stroke="#FEF2F2" vertical={false} />
                            <XAxis dataKey={'name'} />
                            <YAxis stroke={'#FEF2F2'} />
                            <Tooltip
                                cursor={{
                                    stroke: '#FEF0F0',
                                    fill: '#FFF',
                                }}
                                itemStyle={{
                                    textAlign: 'left',
                                    stroke: '#FEF0F0',
                                }}
                            />
                            <Legend height={50} align="left" verticalAlign="bottom" iconType="circle" />
                            <Bar
                                name={'Membres comités locaux'}
                                dataKey={'adherent'}
                                fill={'#6BA0EE'}
                                barSize={10}
                                animationEasing={'ease-in-out'}
                            />
                            <Bar
                                name={'Participants aux événements'}
                                dataKey={'adherentMembre'}
                                fill={'#F8BCBC'}
                                barSize={10}
                                animationEasing={'ease-in-out'}
                            />
                        </BarChart>
                    </ResponsiveContainer>
                </div>
            </div>
        );
    }
}

export default CommitteeContainer;

CommitteeContainer.propTypes = {
    committees: PropTypes.array,
    rankingTitle: PropTypes.string,
    summaryTotal: PropTypes.string,
    summaryDescription: PropTypes.string,
    womanPercentage: PropTypes.number,
    manPercentage: PropTypes.number,
    width: PropTypes.number,
    height: PropTypes.number,
    data: PropTypes.array,
    margin: PropTypes.object,
    stroke: PropTypes.string,
    vertical: PropTypes.bool,
    dataKey: PropTypes.object,
    cursor: PropTypes.object,
    itemStyle: PropTypes.object,
    name: PropTypes.string,
    fill: PropTypes.string,
    barSize: PropTypes.number,
    animationEasing: PropTypes.string,
};
