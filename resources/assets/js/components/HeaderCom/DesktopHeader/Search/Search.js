import React, { Component } from 'react';
import {withRouter} from 'react-router-dom';
import Autosuggest from 'react-autosuggest';
import axios from 'axios';
import dataCode from '../../../../dataCode';
import './Search.css';
import URLs from "../../../../URLs";
import {connect} from 'react-redux';

let searchSug = [];

const getSuggestions = value => {
    const inputValue = value.trim().toLowerCase();
    const inputLength = inputValue.length;

    return inputLength === 0 ? [] : searchSug.filter(lang =>
        lang.name.toLowerCase().slice(0, inputLength) === inputValue
    );
};


const getSuggestionValue = suggestion => suggestion.name;

const renderSuggestion = suggestion => (
    <div>
        {suggestion.name}
    </div>
);

const theme = { input: 'form-control input-border', suggestionsContainer: 'auto-suggest-inputOpen',
    suggestionHighlighted: 'auto-suggest-highlighted', suggestionsList: 'auto-suggest-suggestionsList ',
    suggestion: 'auto-suggest-suggestion'};

class Search extends Component {
    state  = {
        searchKey: '',
        redirect: false,
        category: 'all',
        value: '',
        suggestions: [], categories: [],
    }

    componentDidMount() {
    }

    searchHandler = (event) => {
        event.preventDefault();
        const url = '/search/'+this.state.category+'/'+this.state.searchKey;
        if(this.props.history.location.pathname.includes('search')) {
            this.props.history.push(url);
            window.location.reload();
        } else {
            this.props.history.push(url);
        }
    }


    onChange = (e) => {
        this.setState({[e.target.name]: e.target.value});
    }


    onChangeTest = (event, { newValue }) => {
        if(newValue.length > 3) {
            // console.log(newValue);
            let url = URLs.base_URL+URLs.search_part+newValue;
            axios.get(url)
                .then(response => {
                    console.log(response.data[1]);
                    if(response.data[0] === dataCode.partSearch) {
                        searchSug = [];
                        response.data[1].map((item) => {
                            searchSug.push({name: item.manufacturer_part_number});
                            return null;
                        });
                        // console.log(searchSug);
                    }
                })
                .catch(err => {

                });
        }
        this.setState({
            searchKey: newValue
        });
    };

    onSuggestionsFetchRequested = ({ value }) => { this.setState({ suggestions: getSuggestions(value) }); };

    onSuggestionsClearRequested = () => { this.setState({suggestions: []}); };

    render() {
        const { searchKey, suggestions } = this.state;

        const inputProps = {
            placeholder: 'جست وجو ...',
            value: searchKey,
            onChange: this.onChangeTest
        };
        let categories = this.props.categories.map((item) => {
            if (!( (item.product === "Boxes Enclosures Racks") || (item.product === "Cable Assemblies Coaxial Cables RF") ||
                    (item.product === "Cables Wires") || (item.product === "Connectors Interconnects") ||
                    (item.product === "Fans Thermal Management Thermal Heat Sinks") ||
                    (item.product === "Hardware Fasteners Accessories Board Supports") || (item.product === "Industrial Automation and Controls Machine Safety Light Curtains") ||
                    (item.product === "Industrial Controls Time Delay Relays") || (item.product === "Maker DIY Educational Wearables") ||
                    (item.product === "Power Supplies Board Mount") || (item.product === "Power Supplies External Internal Off Board") ||
                    (item.product === "Relays Solid State Relays") || (item.product === "Development Boards Kits Programmers") ||
                    (item.product === "Static Control ESD Clean Room Products Static Control Clothing") || (item.product === "Test and Measurement") ||
                    (item.product === "Switches Slide Switches") || (item.product === "Switches Toggle Switches") ||
                    (item.product === "Tools") || (item.product === "Uncategorized Miscellaneous") ||
                    (item.product === "Line Protection Distribution Backups Power Distribution Surge Protectors")
                )) {
                return (
                    <option value={item.product} dir="rtl">{item.product}</option>
                )
            }
        });
        return (
        <div className="pt-1 mt-1 ml-5 pl-5 justify-content-center">
            <form onSubmit={this.searchHandler} className="form-inline">
                <div id="desktopSearch" className="input-group">
                    <div className="input-group-prepend">
                        <button className="btn btn-default" style={{height: '37px'}} onClick={this.searchHandler} >
                            <span className="fa fa-search"></span>
                        </button>
                    </div>
                    <Autosuggest theme={theme}
                        suggestions={suggestions}
                        onSuggestionsFetchRequested={this.onSuggestionsFetchRequested}
                        onSuggestionsClearRequested={this.onSuggestionsClearRequested}
                        getSuggestionValue={getSuggestionValue}
                        renderSuggestion={renderSuggestion}
                        inputProps={inputProps}
                    />
                    <div className="input-group-append" dir="rtl">
                        <select className="form-control m-0" style={{height: '37px'}} dir="rtl" value={this.state.category} onChange={this.onChange} name="category">
                            <option value="all" dir="rtl">همه</option>
                            {categories}
                        </select>
                    </div>
                </div>
            </form>
        </div>
        )
    }
}
const mapStateToProps = state => {
    return {
        categories: state.cart.categories,
    };
};


export default withRouter(connect(mapStateToProps, null)(Search));

