import React, { Component } from 'react';

class ProductPrice extends Component {

    state = {
        price: 0, loading: true, number: 1
    }
    componentDidMount() {

    }


    render() {

        return (
            <tr>
                <td>{this.props.keyword}</td>
                <td>{this.props.num}</td>
                <td>{this.props.price}</td>
                <td>{parseInt(this.props.price)*parseInt(this.props.num)}</td>
            </tr>
        )
    }
}


export default ProductPrice;

