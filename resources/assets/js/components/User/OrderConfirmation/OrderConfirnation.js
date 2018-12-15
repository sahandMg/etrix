import React, { Component } from 'react';
import { connect } from 'react-redux';
import CardWrapper from "../../CardWrapper/CardWrapper";
import URLs from "../../../URLs";
import axios from 'axios';

class OrderConfirnation extends Component {
    state = {
        data: {
            address: '', phone: '', codePost: ''
        },
        price: 0, number: '',
        errors: {}
    }

    componentDidMount() {
        console.log("OrderConfirnation");
        axios.post(URLs.base_URL+URLs.user_cart_submit, {token: this.props.token})
            .then(response => {
                console.log("deleteFromCart");console.log(response);
                this.setState({price: response.data.price, number: response.data.number});
            })
            .catch(err => {
                console.log(err);
            });
    }
    onChange = e =>
        this.setState({
            data: { ...this.state.data, [e.target.name]: e.target.value }
        });

    confirmFactor = () => {

    }

    render() {
        console.log("OrderConfirnation render");
        let data = this.state.data;
        return (
            <div className="container" style={{direction: 'rtl'}}>
                <CardWrapper>
                    <h1 className="text-center">پیش فاکتور</h1>
                    <h3 className="text-center">هزینه کل قطعات : {this.state.price} تومان</h3>
                    <h3 className="text-center">هزینه ارسال سفارش : {this.state.price} تومان</h3>
                    <br/>
                    <h3 className="text-center">مبلغ پرداختی سفارش : {this.state.price} تومان</h3>
                    <br/>
                    <button type="submit" className="btn btn-success" style={{display: 'block', margin: 'auto'}}>پرداخت</button>
                </CardWrapper>
                <br/><br/><br/><br/>
            </div>
        )
    }
}

const mapStateToProps = state => {
    return {
        token: state.auth.token,
    };
};

export default connect(mapStateToProps,null)(OrderConfirnation);

