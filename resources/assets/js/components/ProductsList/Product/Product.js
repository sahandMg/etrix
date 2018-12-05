import React, { Component } from 'react';
import axios from 'axios';
import { Link } from 'react-router-dom';
import URLs from '../../../URLs';
// import "./ProductsList.css";

class Product extends Component {
    state = {
        product: {}, loading: true
    }

    componentDidMount() {
        // http://localhost:80/api/search-part-comp?category=all&keyword=STM32F103C8T6TR
        let url = URLs.base_URL;
        axios.get(url)
            .then(response => {
                // console.log("componentDidMount");
                // console.log(response);
                // console.log(dataCode.partSearch);
                if(response.data[0] === dataCode.partSearch) {
                    // console.log("IS EQUAL");
                    this.setState({dataCode: response.data[0],dataParts: response.data[2],dataFilters: response.data[3],tableHeaderS: response.data[5]});
                }
                this.setState({loading: false});
            })
            .catch(err => {
                console.log("componentDidMount searchKey");console.log(err);
            })
    }

    render() {
        let headPart;
        if(Object.keys(this.state.product).length > 0) {
            headPart
        }
        return (
            <div className="container">
                <h1>{this.props.match.params.Product}</h1>

            </div>
        )
    }
}

export default Product;