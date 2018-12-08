import React, { Component } from 'react';
import axios from 'axios';
import { Link } from 'react-router-dom';
import URLs from '../../URLs';
import "./ProductsList.css";

class ProductsList extends Component {
    state = {
        contents: [], videos: []
    }

    componentDidMount() {
        // axios.post('http://localhost:80/api/home')
        //     .then((res) => {
        //         // console.log('res ContainerSmallSize');
        //         // console.log(res);
        //         this.setState({contents: res.data});
        //     })
        //     .catch((error) => {
        //         console.log('error');
        //         console.log(error);
        //     });
        // axios.get(URLs.base_URL + URLs.get_videos)
        //     .then((res) => {
        //         console.log('res get videos');
        //         console.log(res);
        //         this.setState({videos: res.data});
        //     })
        //     .catch((error) => {
        //         console.log('error get videos');
        //         console.log(error);
        //     });
    }

    render() {
        return (
            <div className="product-list">
                <h2>Capacitors</h2>
                <ul>
                    <li><Link to="/search/:category">Accessories</Link></li>
                    <li><Link to="/search/:category">Accessories</Link></li>
                    <li><Link to="/search/:category">Accessories</Link></li>
                    <li><Link to="/search/:category">Accessories</Link></li>
                </ul>
                <h2>Integrated Circuits (ICs)</h2>
                <ul>
                    <li><Link to="/search/:category">Audio Special Purpose</Link></li>
                    <li><Link to="/search/:category">Clock/Timing - Application Specific</Link></li>
                    <li><Link to="/search/:category">Data Acquisition - ADCs/DACs - Special Purpose</Link></li>
                    <li><Link to="/search/:category">Embedded - Microcontrollers - Application Specific</Link></li>
                </ul>
            </div>
        )
    }
}

export default ProductsList;