import React, { Component } from 'react';
import axios from 'axios';
import { Link } from 'react-router-dom';
import URLs from '../../../URLs';
import dataCode from '../../../dataCode';
import product from '../../../assets/product.jpg';
import Comment from '../../Comments/Comments';

class Product extends Component {
    state = {
        searchKey: '', data: '', dataParts: [], dataCode: '', dataFilters: [],open: false, price: 2000, projects: [],
        tableHeaderS: '', productNum: 1, loading: true, number: 1,loadingAddCart: true,productName: '', category: '',
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
                <br/><br/>
                <div className="row">
                    <div className="col-md-6 col-sm-12">
                       <h1 className="text-center">{this.props.match.params.name}</h1>
                       <br/>
                       <div className="row">
                        <div className="col-md-4 col-sm-6">
                          <div style={{margin: "auto",display: "block", float: "right"}}>
                           <img width="100" height="100" src={product}/>.
                           <br/><br/>
                           <div className="flex-row space-between">
                             <span><b>دیتاشیت : </b></span>
                             <Link to=""><i className="fa fa-file-text" aria-hidden="true"></i></Link>
                           </div>
                          </div>
                        </div>
                        <div className="col-md-8 col-sm-6" style={{textAlign: "right"}}>
                            <h5>سازنده</h5>
                            <p>STM Microcontroller</p>
                            <h5>نوع محصول</h5>
                            <p>Microcontroller</p>
                            <h5>توضیحات</h5>
                            <p>MCU 32-bit ARM Cortex M4 RISC 512KB Flash 1.8V/2.5V/3.3V 64-Pin LQFP T/R</p>
                        </div>
                       </div>
                       <br/>
                    </div>
                    <div className="col-md-6 col-sm-12" style={{textAlign: "right"}}>
                        <p style={{fontSize: "20px"}}>حداقل تعداد : 1</p>
                        <p style={{fontSize: "20px"}}>قیمت : 2000 تومان</p>
                        <div className="flex-row">
                            <input className="form-control col-md-4 col-sm-5" name="email" value={this.state.productNum} onChange={(text) => this.setState({productNum: text})}
                                   placeholder="0"/>
                            <button className="btn btn-success margin-right-1">خرید</button>
                        </div>
                        <p style={{fontSize: "20px", direction: "ltr"}}>{this.state.number} * {this.state.price} = {this.state.number*this.state.price}</p>
                    </div>
                </div>
                <div className="row">
                    <div className="col-md-6 col-sm-12">
                        <hr/>
                        <br/>
                        <h2 className="text-center">مشخصات فنی محصول</h2>
                        <table className="table table-striped">
                            <tbody>
                            <tr>
                                <td>John</td>
                                <td>Doe</td>
                            </tr>
                            <tr>
                                <td>Mary</td>
                                <td>Moe</td>
                            </tr>
                            <tr>
                                <td>July</td>
                                <td>Dooley</td>
                            </tr>
                            </tbody>
                        </table>
                    </div>
                    <div  className="col-md-6 col-sm-12">

                    </div>
                </div>
                <br/><br/>
                <Comment/>
            </div>
        )
    }
}

export default Product;