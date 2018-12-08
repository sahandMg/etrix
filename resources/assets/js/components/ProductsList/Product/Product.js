import React, { Component } from 'react';
import axios from 'axios';
import { Link } from 'react-router-dom';
import URLs from '../../../URLs';
import dataCode from '../../../dataCode';
// import "./ProductsList.css";

class Product extends Component {
    state = {
        searchKey: '', data: '', dataParts: [], dataCode: '', dataFilters: [],open: false, prices: {}, projects: [],
        tableHeaderS: '', filters: {}, loading: true, number: 1,loadingAddCart: true,productName: '', category: '',
        projectName: null
    }

    componentDidMount() {
        // http://localhost:80/api/search-part-comp?category=all&keyword=STM32F103C8T6TR
        let url = URLs.base_URL+"/search-part-comp?category=all&keyword="+this.props.match.params.Product;
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
        // if(this.state.dataParts.length === 1) {
        //     headPart = <h1>{this.props.match.params.Product}</h1> ;
        // }
        return (
            <div className="container">
                <h1>{this.props.match.params.Product}</h1>
                <div className="row">
                    <div className="col-md-6 col-sm-12">
                        <img src={}/>
                    </div>
                    <div className="col-md-6 col-sm-12">

                    </div>
                </div>
            </div>
        )
    }
}

export default Product;